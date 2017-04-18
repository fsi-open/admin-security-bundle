<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Mailer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TwigSwiftMailerSpec extends ObjectBehavior
{
    /**
     * @param \Swift_Mailer $mailer
     * @param \FSi\Bundle\AdminSecurityBundle\Mailer\SwiftMessageFactoryInterface $messageFactory
     */
    function let($mailer, $messageFactory)
    {
        $this->beConstructedWith(
            $mailer,
            $messageFactory,
            'mail-template.html.twig',
            'sender@example.com',
            'no-reply@example.com'
        );
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Mailer\EmailableInterface $receiver
     * @param \Swift_Mailer $mailer
     * @param \FSi\Bundle\AdminSecurityBundle\Mailer\SwiftMessageFactoryInterface $messageFactory
     * @param \Swift_Message $message
     */
    function it_sends_message_throught_swift_mailer($receiver, $mailer, $messageFactory, $message)
    {
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
