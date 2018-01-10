<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\Mailer;

use FSi\Bundle\AdminSecurityBundle\Mailer\SwiftMessageFactoryInterface;
use FSi\Bundle\AdminSecurityBundle\Mailer\EmailableInterface;
use PhpSpec\ObjectBehavior;

class TwigSwiftMailerSpec extends ObjectBehavior
{
    function let(\Swift_Mailer $mailer, SwiftMessageFactoryInterface $messageFactory)
    {
        $this->beConstructedWith(
            $mailer,
            $messageFactory,
            'mail-template.html.twig',
            'sender@example.com',
            'no-reply@example.com'
        );
    }

    function it_sends_message_throught_swift_mailer(
        EmailableInterface $receiver,
        \Swift_Mailer $mailer,
        SwiftMessageFactoryInterface $messageFactory,
        \Swift_Message $message
    ) {
        $receiver->getEmail()->willReturn('receiver@example.com');

        $messageFactory->createMessage(
            'receiver@example.com',
            'mail-template.html.twig',
            ['receiver' => $receiver]
        )->willReturn($message);

        $message->setFrom('sender@example.com')->shouldBeCalled();
        $message->setReplyTo('no-reply@example.com')->shouldBeCalled();

        $mailer->send($message)->willReturn(1);

        $this->send($receiver)->shouldReturn(1);
    }
}
