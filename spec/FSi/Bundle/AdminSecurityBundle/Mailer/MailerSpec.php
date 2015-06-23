<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Mailer;

use FSi\Bundle\AdminSecurityBundle\Model\UserPasswordResetInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swift_Mailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig_Environment;
use Twig_Template;

class MailerSpec extends ObjectBehavior
{
    function it_is_bundle()
    {
        $this->shouldHaveType('FSi\Bundle\AdminSecurityBundle\Mailer\Mailer');
    }

    function let(Swift_Mailer $mailer, Twig_Environment $twig, RouterInterface $router, RequestStack $requestStack)
    {
        $this->beConstructedWith(
            $mailer,
            $twig,
            $router,
            $requestStack,
            'mailer-template.html.twig',
            'from@fsi.pl',
            'replay-to@fsi.pl'
        );
    }

    function it_should_render_template(
        UserPasswordResetInterface $user,
        Twig_Environment $twig,
        Twig_Template $template,
        Swift_Mailer $mailer,
        RequestStack $requestStack
    ) {
        $request = new Request(
            array(),
            array(),
            array(),
            array(),
            array(),
            array('HTTP_USER_AGENT' => 'user agent', 'REMOTE_ADDR' => '192.168.99.99')
        );

        $requestStack->getMasterRequest()->willReturn($request);

        $templateParameters = array(
            'user' => $user,
            'ip' => '192.168.99.99',
            'user_agent' => 'user agent'
        );

        $twig->mergeGlobals($templateParameters)->willReturn($templateParameters);
        $twig->loadTemplate('mailer-template.html.twig')->willReturn($template);

        $template->renderBlock('subject', $templateParameters)->willReturn('subject string');
        $template->renderBlock('body_html', $templateParameters)->willReturn('body string');

        $user->getEmail()->willReturn('user@example.com');

        $mailer->send(
            Argument::allOf(
                Argument::type('\Swift_Message'),
                Argument::which('getSubject', 'subject string'),
                Argument::which('getTo', array('user@example.com' => null)),
                Argument::which('getFrom', array('from@fsi.pl' => null)),
                Argument::which('getReplyTo', array('replay-to@fsi.pl' => null)),
                Argument::which('getBody', 'body string')
            )
        )->willReturn(1);

        $this->sendPasswordResetMail($user)->shouldReturn(1);
    }
}
