<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EncodePasswordListenerSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     */
    function let($encoderFactory)
    {
        $this->beConstructedWith($encoderFactory);
    }

    function it_subscribes_change_password_event()
    {
        $this->getSubscribedEvents()->shouldReturn(array(
            AdminSecurityEvents::CHANGE_PASSWORD => 'onChangePassword',
            AdminSecurityEvents::USER_CREATED => 'onUserCreated'
        ));
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserPasswordChangeInterface $user
     */
    function it_does_nothing_if_plain_password_is_not_set($event, $user)
    {
        $event->getUser()->willReturn($user);
        $user->getPlainPassword()->willReturn(null);

        $user->setPassword(Argument::any())->shouldNotBeCalled();

        $this->onChangePassword($event);
    }

    /**
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     * @param \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface $encoder
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     */
    function it_encodes_password_for_user($encoderFactory, $encoder, $event, $user)
    {
        $event->getUser()->willReturn($user);
        $user->getPlainPassword()->willReturn('new-password');
        $encoderFactory->getEncoder($user)->willReturn($encoder);
        $user->getSalt()->willReturn('salt');
        $encoder->encodePassword('new-password', 'salt')->willReturn('encoded-new-password');

        $user->setPassword('encoded-new-password')->shouldBeCalled();
        $user->eraseCredentials()->shouldBeCalled();

        $this->onChangePassword($event);
    }

    /**
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     * @param \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface $encoder
     * @param \FSi\Bundle\AdminSecurityBundle\Event\UserEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     */
    function it_encodes_password_for_new_user($encoderFactory, $encoder, $event, $user)
    {
        $event->getUser()->willReturn($user);
        $user->getPlainPassword()->willReturn('new-password');
        $encoderFactory->getEncoder($user)->willReturn($encoder);
        $user->getSalt()->willReturn('salt');
        $encoder->encodePassword('new-password', 'salt')->willReturn('encoded-new-password');

        $user->setPassword('encoded-new-password')->shouldBeCalled();
        $user->eraseCredentials()->shouldBeCalled();

        $this->onUserCreated($event);
    }
}
