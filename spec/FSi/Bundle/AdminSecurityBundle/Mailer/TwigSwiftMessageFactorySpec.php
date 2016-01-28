<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Mailer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class TwigSwiftMessageFactorySpec extends ObjectBehavior
{
    /**
     * @param \Twig_Environment $twig
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    function let($twig, $requestStack)
    {
        $this->beConstructedWith(
            $twig,
            $requestStack
        );
    }

    /**
     * @param \Twig_Environment $twig
     * @param \Twig_Template $template
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    function it_should_render_template($twig, $template, $requestStack)
    {
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
            'user' => 'user',
            'request' => $request
        );

        $twig->mergeGlobals($templateParameters)->willReturn($templateParameters);
        $twig->loadTemplate('mail-template.html.twig')->willReturn($template);

        $template->renderBlock('subject', $templateParameters)->willReturn('subject string');
        $template->renderBlock('body_html', $templateParameters)->willReturn('body string');

        $message = $this->createMessage('user@example.com', 'mail-template.html.twig', array('user' => 'user'));
        $message->shouldBeAnInstanceOf('\Swift_Message');
        $message->getSubject()->shouldReturn('subject string');
        $message->getTo()->shouldReturn(array('user@example.com' => null));
        $message->getBody()->shouldReturn('body string');
    }
}
