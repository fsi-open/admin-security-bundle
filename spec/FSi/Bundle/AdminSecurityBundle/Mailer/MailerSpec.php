<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Mailer;

use FSi\Bundle\AdminSecurityBundle\Model\UserPasswordResetInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Swift_Mailer;
use Symfony\Component\Routing\RouterInterface;
use Twig_Environment;
use Twig_Template;

class MailerSpec extends ObjectBehavior
{
    function it_is_bundle()
    {
        $this->shouldHaveType('FSi\Bundle\AdminSecurityBundle\Mailer\Mailer');
    }

    function let(Swift_Mailer $mailer, Twig_Environment $twig, RouterInterface $router)
    {
        $this->beConstructedWith(
            $mailer,
            $twig,
            $router,
            'mailer-template.html.twig',
            'from@fsi.pl',
            'replay-to@fsi.pl'
        );
    }

    function it_should_render_template(
        UserPasswordResetInterface $user,
        Twig_Environment $twig,
        Twig_Template $template,
        Swift_Mailer $mailer
    ) {
        $twig->mergeGlobals(array('user' => $user))->willReturn(array('user' => $user));
        $twig->loadTemplate('mailer-template.html.twig')->willReturn($template);

        $template->renderBlock('subject', array('user' => $user))
            ->willReturn('subject string');
        $template->renderBlock('body_html', array('user' => $user))
            ->willReturn('body string');

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
