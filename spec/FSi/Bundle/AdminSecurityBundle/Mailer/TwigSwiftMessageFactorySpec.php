<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\Mailer;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Swift_Message;
use Twig\Environment;
use Twig\Template;

class TwigSwiftMessageFactorySpec extends ObjectBehavior
{
    public function let(Environment $twig, RequestStack $requestStack): void
    {
        $this->beConstructedWith($twig, $requestStack);
    }

    public function it_should_render_template(
        Environment $twig,
        Template $template,
        RequestStack $requestStack
    ): void {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['HTTP_USER_AGENT' => 'user agent', 'REMOTE_ADDR' => '192.168.99.99']
        );

        $requestStack->getMasterRequest()->willReturn($request);

        $templateParameters = ['user' => 'user', 'request' => $request];

        $twig->mergeGlobals($templateParameters)->willReturn($templateParameters);
        $twig->loadTemplate('mail-template.html.twig')->willReturn($template);

        $template->renderBlock('subject', $templateParameters)->willReturn('subject string');
        $template->renderBlock('body_html', $templateParameters)->willReturn('body string');

        $message = $this->createMessage('user@example.com', 'mail-template.html.twig', ['user' => 'user']);
        $message->shouldBeAnInstanceOf(Swift_Message::class);
        $message->getSubject()->shouldReturn('subject string');
        $message->getTo()->shouldReturn(['user@example.com' => null]);
        $message->getBody()->shouldReturn('body string');
    }
}
