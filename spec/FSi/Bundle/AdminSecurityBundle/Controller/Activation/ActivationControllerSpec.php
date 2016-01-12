<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller\Activation;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ActivationControllerSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface $userRepository
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\Form\FormView $formView
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     * @param \FSi\Bundle\AdminBundle\Message\FlashMessages $flashMessages
     */
    function let(
        $templating,
        $userRepository,
        $router,
        $formFactory,
        $form,
        $formView,
        $eventDispatcher,
        $user,
        $flashMessages
    ) {
        $formFactory->create('admin_password_reset_change_password', $user)->willReturn($form);
        $form->createView()->willReturn($formView);

        $this->beConstructedWith(
            $templating,
            'change-password.html.twig',
            $userRepository,
            $router,
            $formFactory,
            $eventDispatcher,
            $flashMessages
        );
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface $userRepository
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    function it_throws_http_not_found_when_token_does_not_exists($userRepository, $request)
    {
        $userRepository->findUserByActivationToken('non-existing-token')->willReturn(null);

        $this->shouldThrow('Symfony\Component\HttpKernel\Exception\NotFoundHttpException')
            ->during('activateAction', array('non-existing-token'));
        $this->shouldThrow('Symfony\Component\HttpKernel\Exception\NotFoundHttpException')
            ->during('changePasswordAction', array($request, 'non-existing-token'));
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface $userRepository
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\User\UserInterface $symfonyUser
     */
    function it_throws_http_not_found_when_user_is_not_supported($userRepository, $request, $symfonyUser)
    {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($symfonyUser);

        $this->shouldThrow('Symfony\Component\HttpKernel\Exception\NotFoundHttpException')
            ->during('activateAction', array('activation-token'));
        $this->shouldThrow('Symfony\Component\HttpKernel\Exception\NotFoundHttpException')
            ->during('changePasswordAction', array($request, 'activation-token'));
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface $userRepository
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface $user
     */
    function it_throws_http_not_found_when_user_is_enabled($userRepository, $request, $user)
    {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(true);

        $this->shouldThrow('Symfony\Component\HttpKernel\Exception\NotFoundHttpException')
            ->during('activateAction', array('activation-token'));
        $this->shouldThrow('Symfony\Component\HttpKernel\Exception\NotFoundHttpException')
            ->during('changePasswordAction', array($request, 'activation-token'));
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface $userRepository
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface $user
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface $token
     */
    function it_throws_http_not_found_when_activation_token_expired($userRepository, $request, $user, $token)
    {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired()->willReturn(false);

        $this->shouldThrow('Symfony\Component\HttpKernel\Exception\NotFoundHttpException')
            ->during('activateAction', array('activation-token'));
        $this->shouldThrow('Symfony\Component\HttpKernel\Exception\NotFoundHttpException')
            ->during('changePasswordAction', array($request, 'activation-token'));
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface $userRepository
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminBundle\Message\FlashMessages $flashMessages
     */
    function it_redirects_to_change_password_if_user_has_enforced_password_change(
        $userRepository, $router, $user, $token, $flashMessages
    ) {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired()->willReturn(true);
        $user->isForcedToChangePassword()->willReturn(true);
        $router->generate('fsi_admin_activation_change_password', array('token' => 'activation-token'))
            ->willReturn('change_password_url');

        $flashMessages->info('admin.activation.message.change_password', 'FSiAdminSecurity')->shouldBeCalled();

        $response = $this->activateAction('activation-token');
        $response->shouldHaveType('Symfony\Component\HttpFoundation\RedirectResponse');
        $response->getTargetUrl()->shouldReturn('change_password_url');
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface $userRepository
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminBundle\Message\FlashMessages $flashMessages
     */
    function it_activates_user(
        $userRepository, $router, $eventDispatcher, $user, $token, $flashMessages
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

        $flashMessages->success('admin.activation.message.success', 'FSiAdminSecurity')->shouldBeCalled();

        $response = $this->activateAction('activation-token');
        $response->shouldHaveType('Symfony\Component\HttpFoundation\RedirectResponse');
        $response->getTargetUrl()->shouldReturn('login_url');
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface $userRepository
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface $token
     */
    function it_throws_http_not_found_during_change_password_if_user_is_not_enforced_to_change_password(
        $userRepository, $request, $user, $token
    ) {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired()->willReturn(true);
        $user->isForcedToChangePassword()->willReturn(false);

        $this->shouldThrow('Symfony\Component\HttpKernel\Exception\NotFoundHttpException')
            ->during('changePasswordAction', array($request, 'activation-token'));
    }

    /**
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface $userRepository
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\Form\FormView $formView
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface $token
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    function it_renders_form_to_change_password(
        $templating, $userRepository, $formFactory, $form, $formView, $request, $user, $token, $response)
    {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired()->willReturn(true);
        $user->isForcedToChangePassword()->willReturn(true);
        $formFactory->create('admin_password_reset_change_password', $user)->willReturn($form);
        $form->isValid()->willReturn(false);
        $templating->renderResponse('change-password.html.twig', array('form' => $formView))->willReturn($response);

        $form->handleRequest($request)->shouldBeCalled();

        $this->changePasswordAction($request, 'activation-token')->shouldReturn($response);
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface $userRepository
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminBundle\Message\FlashMessages $flashMessages
     */
    function it_handles_change_password_form(
        $userRepository, $router, $formFactory, $form, $eventDispatcher, $request, $user, $token, $flashMessages
    ) {
        $userRepository->findUserByActivationToken('activation-token')->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $user->getActivationToken()->willReturn($token);
        $token->isNonExpired()->willReturn(true);
        $user->isForcedToChangePassword()->willReturn(true);
        $formFactory->create('admin_password_reset_change_password', $user)->willReturn($form);
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

        $flashMessages->success('admin.activation.message.change_password_success', 'FSiAdminSecurity')->shouldBeCalled();

        $response = $this->changePasswordAction($request, 'activation-token');
        $response->shouldHaveType('Symfony\Component\HttpFoundation\RedirectResponse');
        $response->getTargetUrl()->shouldReturn('login_url');
    }
}
