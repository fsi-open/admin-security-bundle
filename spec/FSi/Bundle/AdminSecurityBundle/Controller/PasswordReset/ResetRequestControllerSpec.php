<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset;

use FSi\Bundle\AdminBundle\Message\FlashMessages;
use FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset\ResetRequestController;
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Clock\ClockInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class ResetRequestControllerSpec extends ObjectBehavior
{
    public function let(
        Environment $twig,
        FormFactoryInterface $formFactory,
        ClockInterface $clock,
        UrlGeneratorInterface $urlGenerator,
        UserRepositoryInterface $userRepository,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages,
        Request $request,
        FormInterface $form,
        FormInterface $form2,
        UserInterface $user
    ): void {
        $user->isEnabled()->willReturn(true);
        $formFactory->create('form_type')->willReturn($form);
        $form->handleRequest($request)->willReturn($form);
        $form->isSubmitted()->willReturn(true);
        $form->isValid()->willReturn(true);

        $form->get('email')->willReturn($form2);
        $form2->getData()->willReturn('admin@fsi.pl');

        $userRepository->findUserByEmail('admin@fsi.pl')->willReturn($user);

        $user->getPasswordResetToken()->willReturn(null);

        $this->beConstructedWith(
            $twig,
            $formFactory,
            $clock,
            $urlGenerator,
            $userRepository,
            $eventDispatcher,
            $flashMessages,
            'template_path',
            'form_type'
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ResetRequestController::class);
    }

    public function it_updates_confirmation_token_and_dispatches_event(
        Request $request,
        UserInterface $user,
        EventDispatcherInterface $eventDispatcher,
        UrlGeneratorInterface $urlGenerator,
        FlashMessages $flashMessages
    ): void {
        $eventDispatcher->dispatch(
            Argument::allOf(
                Argument::type(ResetPasswordRequestEvent::class),
                Argument::which('getUser', $user->getWrappedObject())
            )
        )->shouldBeCalled();

        $flashMessages->info(
            'admin.password_reset.request.mail_sent_if_correct',
            [],
            'FSiAdminSecurity'
        )->shouldBeCalled();

        $urlGenerator->generate('fsi_admin_security_user_login', [])->willReturn('url');

        $response = $this->__invoke($request);
        $response->shouldHaveType(RedirectResponse::class);
    }

    public function it_does_not_dispatch_event_and_displays_warning_when_user_disabled(
        Request $request,
        UserInterface $user,
        EventDispatcherInterface $eventDispatcher,
        UrlGeneratorInterface $urlGenerator,
        FlashMessages $flashMessages
    ): void {
        $user->isEnabled()->willReturn(false);

        $eventDispatcher->dispatch(
            Argument::allOf(
                Argument::type(ResetPasswordRequestEvent::class),
                Argument::which('getUser', $user->getWrappedObject())
            )
        )->shouldNotBeCalled();

        $flashMessages->info(
            'admin.password_reset.request.mail_sent_if_correct',
            [],
            'FSiAdminSecurity'
        )->shouldBeCalled();

        $urlGenerator->generate('fsi_admin_security_user_login', [])->willReturn('url');

        $response = $this->__invoke($request);
        $response->shouldHaveType(RedirectResponse::class);
    }
}
