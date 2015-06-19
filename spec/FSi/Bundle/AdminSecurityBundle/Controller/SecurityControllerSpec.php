<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller;

use PhpSpec\ObjectBehavior;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityControllerSpec extends ObjectBehavior
{
    function let(
        EngineInterface $templating,
        AuthenticationUtils $authenticationUtils
    ) {
        $this->beConstructedWith($templating, 'FSiAdminSecurityBundle:Security:login.html.twig', $authenticationUtils);
    }

    function it_render_login_template_in_login_action(
        EngineInterface $templating,
        AuthenticationUtils $authenticationUtils,
        Response $response
    ) {
        $error = new \Exception('message');
        $authenticationUtils->getLastAuthenticationError()->willReturn($error);
        $authenticationUtils->getLastUsername()->willReturn('user');
        $templating->renderResponse(
                'FSiAdminSecurityBundle:Security:login.html.twig',
                array(
                    'error' => $error,
                    'last_username' => 'user'
                )
            )->willReturn($response);

        $this->loginAction()->shouldReturn($response);
    }
}
