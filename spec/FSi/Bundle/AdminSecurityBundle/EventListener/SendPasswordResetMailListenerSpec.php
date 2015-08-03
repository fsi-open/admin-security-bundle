<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SendPasswordResetMailListenerSpec extends ObjectBehavior
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
            AdminSecurityEvents::RESET_PASSWORD_REQUEST => 'onResetPasswordRequest'
        ));
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface $mailer
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface $tokenFactory
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Model\TokenInterface $token
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Model\ResettablePasswordInterface $user
     */
    function it_sends_email($mailer, $tokenFactory, $token, $event, $user)
    {
        $event->getUser()->willReturn($user);
        $tokenFactory->createToken()->willReturn($token);

        $user->setPasswordResetToken($token)->shouldBeCalled();
        $mailer->send($user)->shouldBeCalled();

        $this->onResetPasswordRequest($event);
    }
}
