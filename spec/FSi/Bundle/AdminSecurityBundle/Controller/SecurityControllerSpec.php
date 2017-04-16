<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller;

use PhpSpec\ObjectBehavior;

class SecurityControllerSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authenticationUtils
     */
    function let($templating, $authenticationUtils)
    {
        $this->beConstructedWith($templating, $authenticationUtils, 'FSiAdminSecurityBundle:Security:login.html.twig');
    }

    /**
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authenticationUtils
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    function it_render_login_template_in_login_action($templating, $authenticationUtils, $response)
    {
        $error = new \Exception('message');
        $authenticationUtils->getLastAuthenticationError()->willReturn($error);
        $authenticationUtils->getLastUsername()->willReturn('user');
        $templating->renderResponse(
                'FSiAdminSecurityBundle:Security:login.html.twig',
                [
                    'error' => $error,
                    'last_username' => 'user'
                ]
            )->willReturn($response);

        $this->loginAction()->shouldReturn($response);
    }
}
