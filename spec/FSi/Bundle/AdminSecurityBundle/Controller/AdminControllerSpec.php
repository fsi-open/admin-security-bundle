<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use PhpSpec\ObjectBehavior;

class AdminControllerSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher
     */
    function let($templating, $form, $tokenStorage, $router, $eventDispatcher)
    {
        $this->beConstructedWith(
            $templating,
            $form,
            $tokenStorage,
            $router,
            $eventDispatcher,
            'FSiAdminSecurityBundle:Admin:change_password.html.twig'
        );
    }

    /**
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\Form\FormView $formView
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    function it_render_template_with_change_password_form($templating, $form, $formView, $request, $response)
    {
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->shouldBeCalled()->willReturn(false);
        $form->createView()->shouldBeCalled()->willReturn($formView);

        $templating->renderResponse('FSiAdminSecurityBundle:Admin:change_password.html.twig', array(
            'form' => $formView
        ))->shouldBeCalled()->willReturn($response);

        $this->changePasswordAction($request)->shouldReturn($response);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag $flashBag
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     */
    function it_dispatch_event_and_redirect_user_to_login_page_after_successful_form_validation(
        $form, $request, $session, $tokenStorage, $flashBag, $router, $eventDispatcher, $token, $user
    ) {
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->shouldBeCalled()->willReturn(true);
        $request->getSession()->shouldBeCalled()->willReturn($session);

        $form->getData()->shouldBeCalled()->willReturn(
            array(
                'plainPassword' => 'plain_password'
            )
        );
        $token->getUser()->shouldBeCalled()->willReturn($user);
        $event = new ChangePasswordEvent($user->getWrappedObject(), 'plain_password');
        $eventDispatcher->dispatch(AdminSecurityEvents::CHANGE_PASSWORD, $event)->shouldBeCalled();

        $tokenStorage->getToken()->shouldBeCalled()->willReturn($token);
        $tokenStorage->setToken(null)->shouldBeCalled();
        $session->invalidate()->shouldBeCalled();
        $session->getFlashBag()->shouldBeCalled()->willReturn($flashBag);

        $flashBag->set(
            'success',
            'admin.change_password_message.success'
        )->shouldBeCalled();
        $router->generate('fsi_admin_security_user_login')->shouldBeCalled()->willReturn('/admin/login');

        $this->changePasswordAction($request)
            ->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse');
    }
}
