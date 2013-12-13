<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;

class AdminControllerSpec extends ObjectBehavior
{
    function let(EngineInterface $templating, FormInterface $form, SecurityContext $securityContext, Router $router)
    {
        $this->beConstructedWith($templating, $form, $securityContext, $router);
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

    function it_redirect_user_to_login_page_after_successful_form_validation(
        FormInterface $form,
        Request $request,
        Session $session,
        SecurityContext $securityContext,
        ParameterBag $flashBag,
        Router $router
    ) {
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->shouldBeCalled()->willReturn(true);
        $request->getSession()->shouldBeCalled()->willReturn($session);

        $session->invalidate()->shouldBeCalled();
        $securityContext->setToken(null)->shouldBeCalled();
        $session->getFlashBag()->shouldBeCalled()->willReturn($flashBag);

        $flashBag->set(
            'success',
            'admin.change_password_message.success'
        )->shouldBeCalled();

        $router->generate('admin_security_user_login')->shouldBeCalled()->willReturn('/admin/login');

        $this->changePasswordAction($request)->shouldReturnAnInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse');
    }
}
