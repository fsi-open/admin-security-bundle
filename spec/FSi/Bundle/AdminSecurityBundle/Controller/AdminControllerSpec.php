<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminControllerSpec extends ObjectBehavior
{
    function let(
        EngineInterface $templating,
        FormInterface $form,
        SecurityContext $securityContext,
        RouterInterface $router,
        EventDispatcher $eventDispatcher
    ) {
        $this->beConstructedWith(
            $templating,
            $form,
            $securityContext,
            $router,
            $eventDispatcher,
            'FSiAdminSecurityBundle:Admin:change_password.html.twig'
        );
    }

    function it_render_template_with_change_password_form(
        EngineInterface $templating,
        FormInterface $form,
        FormView $formView,
        Request $request,
        Response $response
    ) {
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->shouldBeCalled()->willReturn(false);
        $form->createView()->shouldBeCalled()->willReturn($formView);

        $templating->renderResponse('FSiAdminSecurityBundle:Admin:change_password.html.twig', array(
            'form' => $formView
        ))->shouldBeCalled()->willReturn($response);

        $this->changePasswordAction($request)->shouldReturn($response);
    }

    function it_dispatch_event_and_redirect_user_to_login_page_after_successful_form_validation(
        FormInterface $form,
        Request $request,
        Session $session,
        SecurityContext $securityContext,
        ParameterBag $flashBag,
        RouterInterface $router,
        EventDispatcher $eventDispatcher,
        TokenInterface $token,
        UserInterface $user
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

        $securityContext->getToken()->shouldBeCalled()->willReturn($token);
        $securityContext->setToken(null)->shouldBeCalled();
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
