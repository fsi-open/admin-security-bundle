<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class DoctrineChangePasswordListenerSpec extends ObjectBehavior
{
    function let(Registry $registry, EncoderFactoryInterface $encodeFactory)
    {
        $this->beConstructedWith($registry, $encodeFactory);
    }

    function it_do_nothing_when_user_is_not_doctrine_entity(
        ChangePasswordEvent $event,
        Registry $registry
    ) {
        $registry->getManagerForClass(
            'FSi\Bundle\AdminSecurityBundle\spec\fixtures\User'
        )->willReturn(null);
        $event->getUser()->shouldBeCalled()->willReturn(new User());

        $this->onChangePassword($event)->shouldReturn(null);
    }

    function it_set_password_when_user_is_doctrine_entity(
        ChangePasswordEvent $event,
        Registry $registry,
        User $user,
        EncoderFactoryInterface $encodeFactory,
        PasswordEncoderInterface $encoder
    ) {
        $prophet = new Prophet();
        $em = $prophet->prophesize('Doctrine\ORM\EntityManager');

        $registry->getManagerForClass(
            Argument::type('string')
        )->willReturn($em);

        $event->getUser()->shouldBeCalled()->willReturn($user);
        $event->getPlainPassword()->shouldBeCalled()->willReturn('plain_password');

        $user->getSalt()->shouldBeCalled()->willReturn('salt');
        $encodeFactory->getEncoder($user)->shouldBeCalled()->willReturn($encoder);
        $encoder->encodePassword('plain_password', 'salt')
            ->shouldBeCalled()
            ->willReturn('encoded_password');

        $user->setPassword('encoded_password')->shouldBeCalled();

        $em->persist($user->getWrappedObject())->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->onChangePassword($event);
    }
}
