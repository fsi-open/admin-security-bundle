<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SendActivationMailListenerSpec extends ObjectBehavior
{
    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface $mailer
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface $tokenFactory
     */
    function let($mailer, $tokenFactory)
    {
        $this->beConstructedWith($mailer, $tokenFactory);
    }

    function it_subscribes_user_created_event()
    {
        $this->getSubscribedEvents()->shouldReturn(array(
            AdminSecurityEvents::USER_CREATED => 'onUserCreated'
        ));
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface $mailer
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface $tokenFactory
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminSecurityBundle\Event\UserEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface $user
     */
    function it_sends_email_if_user_is_not_enabled($mailer, $tokenFactory, $token, $event, $user)
    {
        $user->isEnabled()->willReturn(false);
        $event->getUser()->willReturn($user);
        $tokenFactory->createToken()->willReturn($token);

        $user->setActivationToken($token)->shouldBeCalled();
        $mailer->send($user)->shouldBeCalled();

        $this->onUserCreated($event);
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface $mailer
     * @param \FSi\Bundle\AdminSecurityBundle\Event\UserEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface $user
     */
    function it_does_not_send_email_if_user_is_enabled($mailer, $event, $user)
    {
        $user->isEnabled()->willReturn(true);
        $event->getUser()->willReturn($user);

        $mailer->send(Argument::any())->shouldNotBeCalled();

        $this->onUserCreated($event);
    }
}
