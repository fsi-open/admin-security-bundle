<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller\Activation;

use FSi\Bundle\AdminBundle\Message\FlashMessages;
use FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use TypeError;

class ActivateControllerSpec extends ObjectBehavior
{
    public function let(
        UserRepositoryInterface $userRepository,
        ClockInterface $clock,
        EventDispatcherInterface $eventDispatcher,
        UrlGeneratorInterface $urlGenerator,
        FlashMessages $flashMessages
    ): void {
        $this->beConstructedWith(
            $userRepository,
            $clock,
            $eventDispatcher,
            $urlGenerator,
            $flashMessages
        );
    }

    public function it_throws_http_not_found_when_token_does_not_exists(
        UserRepositoryInterface $userRepository
    ): void {
        $userRepository->findUserByActivationToken('non-existing-token')->willReturn(null);

        $this->shouldThrow(NotFoundHttpException::class)->during('__invoke', ['non-existing-token']);
    }

    public function it_throws_type_error_when_user_is_not_supported(
        UserRepositoryInterface $userRepository,
        SymfonyUserInterface $symfonyUser
    ): void {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($symfonyUser);

        $this->shouldThrow(TypeError::class)->during('__invoke', ['activation-token']);
    }

    public function it_throws_http_not_found_when_user_is_enabled(
        UserRepositoryInterface $userRepository,
        ActivableInterface $user
    ): void {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(true);

        $this->shouldThrow(NotFoundHttpException::class)->during('__invoke', ['activation-token']);
    }

    public function it_throws_http_not_found_when_activation_token_expired(
        UserRepositoryInterface $userRepository,
        ClockInterface $clock,
        UserInterface $user,
        TokenInterface $token
    ): void {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired($clock)->willReturn(false);

        $this->shouldThrow(NotFoundHttpException::class)->during('__invoke', ['activation-token']);
    }

    public function it_redirects_to_change_password_if_user_has_enforced_password_change(
        UserRepositoryInterface $userRepository,
        UrlGeneratorInterface $urlGenerator,
        ClockInterface $clock,
        UserInterface $user,
        TokenInterface $token,
        FlashMessages $flashMessages
    ): void {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired($clock)->willReturn(true);
        $user->isForcedToChangePassword()->willReturn(true);
        $flashMessages->info('admin.activation.message.change_password', [], 'FSiAdminSecurity')->shouldBeCalled();
        $urlGenerator
            ->generate('fsi_admin_activation_change_password', ['token' => 'activation-token'])
            ->willReturn('change_password_url')
        ;

        $response = $this->__invoke('activation-token');
        $response->shouldHaveType(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('change_password_url');
    }

    public function it_activates_user(
        UserRepositoryInterface $userRepository,
        UrlGeneratorInterface $urlGenerator,
        ClockInterface $clock,
        EventDispatcherInterface $eventDispatcher,
        UserInterface $user,
        TokenInterface $token,
        FlashMessages $flashMessages
    ): void {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired($clock)->willReturn(true);
        $user->isForcedToChangePassword()->willReturn(false);
        $urlGenerator->generate('fsi_admin_security_user_login', [])->willReturn('login_url');

        $eventDispatcher->dispatch(
            Argument::allOf(
                Argument::type(ActivationEvent::class),
                Argument::which('getUser', $user->getWrappedObject())
            )
        )->shouldBeCalled();

        $flashMessages->success('admin.activation.message.success', [], 'FSiAdminSecurity')->shouldBeCalled();

        $response = $this->__invoke('activation-token');
        $response->shouldHaveType(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('login_url');
    }
}
