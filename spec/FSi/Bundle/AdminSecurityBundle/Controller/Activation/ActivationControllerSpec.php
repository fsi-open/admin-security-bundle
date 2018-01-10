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
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

class ActivationControllerSpec extends ObjectBehavior
{
    function let(
        EngineInterface $templating,
        UserRepositoryInterface $userRepository,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        FormInterface $form,
        FormView $formView,
        EventDispatcherInterface $eventDispatcher,
        UserInterface $user,
        FlashMessages $flashMessages,
        Request $request
    ) {
        $formFactory->create(
            'form_type',
            $user,
            ['validation_groups' => ['validation_group']]
        )->willReturn($form);
        $form->createView()->willReturn($formView);
        $form->handleRequest($request)->willReturn($form);
        $form->isSubmitted()->willReturn(true);

        $this->beConstructedWith(
            $templating,
            'change-password.html.twig',
            $userRepository,
            $router,
            $formFactory,
            $eventDispatcher,
            $flashMessages,
            'form_type',
            ['validation_group']
        );
    }

    function it_throws_http_not_found_when_token_does_not_exists(
        UserRepositoryInterface $userRepository,
        Request $request
    ) {
        $userRepository->findUserByActivationToken('non-existing-token')->willReturn(null);

        $this->shouldThrow(NotFoundHttpException::class)
            ->during('activateAction', ['non-existing-token']);
        $this->shouldThrow(NotFoundHttpException::class)
            ->during('changePasswordAction', [$request, 'non-existing-token']);
    }

    function it_throws_type_error_when_user_is_not_supported(
        UserRepositoryInterface $userRepository,
        Request $request,
        SymfonyUserInterface $symfonyUser
    ) {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($symfonyUser);

        $this->shouldThrow(\TypeError::class)
            ->during('activateAction', ['activation-token']);
        $this->shouldThrow(\TypeError::class)
            ->during('changePasswordAction', [$request, 'activation-token']);
    }

    function it_throws_http_not_found_when_user_is_enabled(
        UserRepositoryInterface $userRepository,
        Request $request,
        ActivableInterface $user
    ) {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(true);

        $this->shouldThrow(NotFoundHttpException::class)
            ->during('activateAction', ['activation-token']);
        $this->shouldThrow(NotFoundHttpException::class)
            ->during('changePasswordAction', [$request, 'activation-token']);
    }

    function it_throws_http_not_found_when_activation_token_expired(
        UserRepositoryInterface $userRepository,
        Request $request,
        UserInterface $user,
        TokenInterface $token
    ) {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired()->willReturn(false);

        $this->shouldThrow(NotFoundHttpException::class)
            ->during('activateAction', ['activation-token']);
        $this->shouldThrow(NotFoundHttpException::class)
            ->during('changePasswordAction', [$request, 'activation-token']);
    }

    function it_redirects_to_change_password_if_user_has_enforced_password_change(
        UserRepositoryInterface $userRepository,
        RouterInterface $router,
        UserInterface $user,
        TokenInterface $token,
        FlashMessages $flashMessages
    ) {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired()->willReturn(true);
        $user->isForcedToChangePassword()->willReturn(true);
        $router->generate('fsi_admin_activation_change_password', ['token' => 'activation-token'])
            ->willReturn('change_password_url');

        $flashMessages->info('admin.activation.message.change_password', [], 'FSiAdminSecurity')->shouldBeCalled();

        $response = $this->activateAction('activation-token');
        $response->shouldHaveType(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('change_password_url');
    }

    function it_activates_user(
        UserRepositoryInterface $userRepository,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        UserInterface $user,
        TokenInterface $token,
        FlashMessages $flashMessages
    ) {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired()->willReturn(true);
        $user->isForcedToChangePassword()->willReturn(false);
        $router->generate('fsi_admin_security_user_login')->willReturn('login_url');

        $eventDispatcher->dispatch(AdminSecurityEvents::ACTIVATION, Argument::allOf(
            Argument::type('FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent'),
            Argument::which('getUser', $user->getWrappedObject())
        ))->shouldBeCalled();

        $flashMessages->success('admin.activation.message.success', [], 'FSiAdminSecurity')->shouldBeCalled();

        $response = $this->activateAction('activation-token');
        $response->shouldHaveType(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('login_url');
    }

    function it_throws_http_not_found_during_change_password_if_user_is_not_enforced_to_change_password(
        UserRepositoryInterface $userRepository,
        Request $request,
        UserInterface $user,
        TokenInterface $token
    ) {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired()->willReturn(true);
        $user->isForcedToChangePassword()->willReturn(false);

        $this->shouldThrow(NotFoundHttpException::class)
            ->during('changePasswordAction', [$request, 'activation-token']);
    }

    function it_renders_form_to_change_password(
        EngineInterface $templating,
        UserRepositoryInterface $userRepository,
        FormFactoryInterface $formFactory,
        FormInterface $form,
        FormView $formView,
        Request $request,
        UserInterface $user,
        TokenInterface $token,
        Response $response
    ) {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired()->willReturn(true);
        $user->isForcedToChangePassword()->willReturn(true);
        $formFactory->create(
            'form_type',
            $user,
            ['validation_groups' => ['validation_group']]
        )->willReturn($form);
        $form->isValid()->willReturn(false);
        $templating->renderResponse('change-password.html.twig', ['form' => $formView])->willReturn($response);

        $form->handleRequest($request)->shouldBeCalled();

        $this->changePasswordAction($request, 'activation-token')->shouldReturn($response);
    }

    function it_handles_change_password_form(
        UserRepositoryInterface $userRepository,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        FormInterface $form,
        EventDispatcherInterface $eventDispatcher,
        Request $request,
        UserInterface $user,
        TokenInterface $token,
        FlashMessages $flashMessages
    ) {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired()->willReturn(true);
        $user->isForcedToChangePassword()->willReturn(true);
        $formFactory->create(
            'form_type',
            $user,
            ['validation_groups' => ['validation_group']]
        )->willReturn($form);
        $form->isValid()->willReturn(true);
        $router->generate('fsi_admin_security_user_login')->willReturn('login_url');

        $form->handleRequest($request)->shouldBeCalled();
        $eventDispatcher->dispatch(AdminSecurityEvents::ACTIVATION, Argument::allOf(
            Argument::type('FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent'),
            Argument::which('getUser', $user->getWrappedObject())
        ))->shouldBeCalled();
        $eventDispatcher->dispatch(AdminSecurityEvents::CHANGE_PASSWORD, Argument::allOf(
            Argument::type('FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent'),
            Argument::which('getUser', $user->getWrappedObject())
        ))->shouldBeCalled();

        $flashMessages->success(
            'admin.activation.message.change_password_success',
            [],
            'FSiAdminSecurity'
        )->shouldBeCalled();

        $response = $this->changePasswordAction($request, 'activation-token');
        $response->shouldHaveType(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('login_url');
    }
}
