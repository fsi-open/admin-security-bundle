<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SendActivationMailListenerSpec extends ObjectBehavior
{
    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface $mailer
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface $tokenFactory
     */
    function let(
        MailerInterface $mailer,
        TokenFactoryInterface $tokenFactory
    ) {
        $this->beConstructedWith($mailer, $tokenFactory);
    }

    function it_subscribes_user_created_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            AdminSecurityEvents::USER_CREATED => 'onUserCreated'
        ]);
    }

    function it_sends_email_if_user_is_not_enabled(
        MailerInterface $mailer,
        TokenFactoryInterface $tokenFactory,
        TokenInterface $token,
        UserEvent $event,
        ActivableInterface $user
    ) {
        $user->isEnabled()->willReturn(false);
        $event->getUser()->willReturn($user);
        $tokenFactory->createToken()->willReturn($token);

        $user->setActivationToken($token)->shouldBeCalled();
        $mailer->send($user)->shouldBeCalled();

        $this->onUserCreated($event);
    }

    function it_does_not_send_email_if_user_is_enabled(
        MailerInterface $mailer,
        UserEvent $event,
        ActivableInterface $user
    ) {
        $user->isEnabled()->willReturn(true);
        $event->getUser()->willReturn($user);

        $mailer->send(Argument::any())->shouldNotBeCalled();

        $this->onUserCreated($event);
    }
}
