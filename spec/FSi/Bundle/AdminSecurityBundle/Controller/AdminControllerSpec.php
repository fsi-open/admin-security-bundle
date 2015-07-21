<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AdminControllerSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher
     */
    function let($templating, $formFactory, $tokenStorage, $router, $eventDispatcher)
    {
        $this->beConstructedWith(
            $templating,
            $formFactory,
            $tokenStorage,
            $router,
            $eventDispatcher,
            'FSiAdminSecurityBundle:Admin:change_password.html.twig'
        );
    }

    /**
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserPasswordChangeInterface $user
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\Form\FormView $formView
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    function it_render_template_with_change_password_form(
        $templating, $formFactory, $tokenStorage, $token, $user, $form, $formView, $request, $response
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $formFactory->create('admin_change_password', $user)->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->shouldBeCalled()->willReturn(false);
        $form->createView()->shouldBeCalled()->willReturn($formView);

        $templating->renderResponse('FSiAdminSecurityBundle:Admin:change_password.html.twig', array(
            'form' => $formView
        ))->shouldBeCalled()->willReturn($response);

        $this->changePasswordAction($request)->shouldReturn($response);
    }

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserPasswordChangeInterface $user
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag $flashBag
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher
     */
    function it_dispatch_event_and_redirect_user_to_login_page_after_successful_form_validation(
        $formFactory, $tokenStorage, $token, $user, $form, $request, $session, $flashBag, $router, $eventDispatcher
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $formFactory->create('admin_change_password', $user)->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);
        $request->getSession()->willReturn($session);

        $token->getUser()->shouldBeCalled()->willReturn($user);
        $eventDispatcher->dispatch(
            AdminSecurityEvents::CHANGE_PASSWORD,
            Argument::allOf(
                Argument::type('FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent'),
                Argument::which('getUser', $user->getWrappedObject())
            )
        )->shouldBeCalled();

        $tokenStorage->setToken(null)->shouldBeCalled();
        $session->invalidate()->shouldBeCalled();
        $session->getFlashBag()->shouldBeCalled()->willReturn($flashBag);

        $flashBag->set(
            'success',
            'admin.change_password_message.success'
        )->shouldBeCalled();
        $router->generate('fsi_admin_security_user_login')->shouldBeCalled()->willReturn('/admin/login');

        $response = $this->changePasswordAction($request);
        $response->shouldHaveType('Symfony\Component\HttpFoundation\RedirectResponse');
        $response->getTargetUrl()->shouldReturn('/admin/login');
    }
}
