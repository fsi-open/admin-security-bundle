<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityControllerSpec extends ObjectBehavior
{
    function let(
        EngineInterface $templating,
        SessionInterface $session,
        Request $request,
        ParameterBag $requestAttributes
    ) {
        $requestAttributes->has(SecurityContext::AUTHENTICATION_ERROR)->shouldBeCalled()->willReturn(false);
        $request->attributes = $requestAttributes;
        $request->getSession()->willReturn($session);

        $this->beConstructedWith($templating);
    }

    function it_render_login_template_in_login_action(EngineInterface $templating, Response $response, Request $request)
    {
        $templating->renderResponse(
                '@FSiAdminSecurity/Security/login.html.twig',
                array(
                    'error' => null,
                    'csrf_token' => null
                )
            )->shouldBeCalled()
            ->willReturn($response);

        $this->loginAction($request)->shouldReturn($response);
    }

    function it_render_login_template_with_authentication_error_when_error_occurs_after_forward_or_redirect(
        EngineInterface $templating,
        Request $request,
        Response $response,
        ParameterBag $requestAttributes
    ){
        $requestAttributes->has(SecurityContext::AUTHENTICATION_ERROR)->shouldBeCalled()->willReturn(true);
        $requestAttributes->get(SecurityContext::AUTHENTICATION_ERROR)->shouldBeCalled()->willReturn('error message');

        $templating->renderResponse(
                '@FSiAdminSecurity/Security/login.html.twig',
                array(
                    'error' => 'error message',
                    'csrf_token' => null
                )
            )->shouldBeCalled()
            ->willReturn($response);

        $this->loginAction($request)->shouldReturn($response);
    }

    function it_render_login_template_with_authentication_error_when_error_occurs_during_login_action(
        EngineInterface $templating,
        Request $request,
        Response $response,
        SessionInterface $session
    ){
        $session->has(SecurityContext::AUTHENTICATION_ERROR)->shouldBeCalled()->willReturn(true);
        $session->get(SecurityContext::AUTHENTICATION_ERROR)->shouldBeCalled()->willReturn('error message');
        $session->remove(SecurityContext::AUTHENTICATION_ERROR)->shouldBeCalled();

        $templating->renderResponse(
                '@FSiAdminSecurity/Security/login.html.twig',
                array(
                    'error' => 'error message',
                    'csrf_token' => null
                )
            )->shouldBeCalled()
            ->willReturn($response);

        $this->loginAction($request)->shouldReturn($response);
    }

    function it_render_login_template_with_csrf_token(
        EngineInterface $templating,
        Request $request,
        Response $response,
        CsrfProviderInterface $csrfProvider
    ) {
        $csrfProvider->generateCsrfToken('authenticate')->shouldBeCalled()->willReturn('token');
        $this->beConstructedWith($templating, $csrfProvider);

        $templating->renderResponse(
                '@FSiAdminSecurity/Security/login.html.twig',
                array(
                    'error' => null,
                    'csrf_token' => 'token'
                )
            )->shouldBeCalled()
            ->willReturn($response);

        $this->loginAction($request)->shouldReturn($response);
    }
}
