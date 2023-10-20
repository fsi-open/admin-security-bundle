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
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Clock\ClockInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use TypeError;

class ChangePasswordControllerSpec extends ObjectBehavior
{
    public function let(
        Environment $twig,
        UserRepositoryInterface $userRepository,
        ClockInterface $clock,
        UrlGeneratorInterface $urlGenerator,
        FormFactoryInterface $formFactory,
        FormInterface $form,
        FormView $formView,
        EventDispatcherInterface $eventDispatcher,
        UserInterface $user,
        FlashMessages $flashMessages,
        Request $request
    ): void {
        $formFactory->create(
            'form_type',
            $user,
            ['validation_groups' => ['validation_group']]
        )->willReturn($form);
        $form->createView()->willReturn($formView);
        $form->handleRequest($request)->willReturn($form);
        $form->isSubmitted()->willReturn(true);

        $this->beConstructedWith(
            $twig,
            $userRepository,
            $clock,
            $urlGenerator,
            $formFactory,
            $eventDispatcher,
            $flashMessages,
            'change-password.html.twig',
            'form_type',
            ['validation_group']
        );
    }

    public function it_throws_http_not_found_when_token_does_not_exists(
        UserRepositoryInterface $userRepository,
        Request $request
    ): void {
        $userRepository->findUserByActivationToken('non-existing-token')->willReturn(null);

        $this->shouldThrow(NotFoundHttpException::class)->during('__invoke', [$request, 'non-existing-token']);
    }

    public function it_throws_type_error_when_user_is_not_supported(
        UserRepositoryInterface $userRepository,
        Request $request,
        SymfonyUserInterface $symfonyUser
    ): void {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($symfonyUser);

        $this->shouldThrow(TypeError::class)->during('__invoke', [$request, 'activation-token']);
    }

    public function it_throws_http_not_found_when_user_is_enabled(
        UserRepositoryInterface $userRepository,
        Request $request,
        ActivableInterface $user
    ): void {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(true);

        $this->shouldThrow(NotFoundHttpException::class)->during('__invoke', [$request, 'activation-token']);
    }

    public function it_throws_http_not_found_when_activation_token_expired(
        UserRepositoryInterface $userRepository,
        ClockInterface $clock,
        Request $request,
        UserInterface $user,
        TokenInterface $token
    ): void {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired($clock)->willReturn(false);

        $this->shouldThrow(NotFoundHttpException::class)->during('__invoke', [$request, 'activation-token']);
    }

    public function it_throws_http_not_found_during_change_password_if_user_is_not_enforced_to_change_password(
        UserRepositoryInterface $userRepository,
        ClockInterface $clock,
        Request $request,
        UserInterface $user,
        TokenInterface $token
    ): void {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired($clock)->willReturn(true);
        $user->isForcedToChangePassword()->willReturn(false);

        $this->shouldThrow(NotFoundHttpException::class)->during('__invoke', [$request, 'activation-token']);
    }

    public function it_renders_form_to_change_password(
        Environment $twig,
        UserRepositoryInterface $userRepository,
        ClockInterface $clock,
        FormFactoryInterface $formFactory,
        FormInterface $form,
        FormView $formView,
        Request $request,
        UserInterface $user,
        TokenInterface $token
    ): void {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired($clock)->willReturn(true);
        $user->isForcedToChangePassword()->willReturn(true);
        $formFactory->create(
            'form_type',
            $user,
            ['validation_groups' => ['validation_group']]
        )->willReturn($form);
        $form->isValid()->willReturn(false);
        $twig->render('change-password.html.twig', ['form' => $formView])->willReturn('response');

        $form->handleRequest($request)->shouldBeCalled();

        $this->__invoke($request, 'activation-token')->getContent()->shouldReturn('response');
    }

    public function it_handles_change_password_form(
        UserRepositoryInterface $userRepository,
        ClockInterface $clock,
        UrlGeneratorInterface $urlGenerator,
        FormFactoryInterface $formFactory,
        FormInterface $form,
        EventDispatcherInterface $eventDispatcher,
        Request $request,
        UserInterface $user,
        TokenInterface $token,
        FlashMessages $flashMessages
    ): void {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired($clock)->willReturn(true);
        $user->isForcedToChangePassword()->willReturn(true);
        $formFactory->create(
            'form_type',
            $user,
            ['validation_groups' => ['validation_group']]
        )->willReturn($form);
        $form->isValid()->willReturn(true);
        $urlGenerator->generate('fsi_admin_security_user_login', [])->willReturn('login_url');

        $form->handleRequest($request)->shouldBeCalled();
        $eventDispatcher->dispatch(
            Argument::allOf(
                Argument::type(ActivationEvent::class),
                Argument::which('getUser', $user->getWrappedObject())
            )
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            Argument::allOf(
                Argument::type(ChangePasswordEvent::class),
                Argument::which('getUser', $user->getWrappedObject())
            )
        )->shouldBeCalled();

        $flashMessages->success(
            'admin.activation.message.change_password_success',
            [],
            'FSiAdminSecurity'
        )->shouldBeCalled();

        $response = $this->__invoke($request, 'activation-token');
        $response->shouldHaveType(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('login_url');
    }
}
