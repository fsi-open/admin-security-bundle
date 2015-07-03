<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\spec\fixtures\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;

class DoctrineChangePasswordListenerSpec extends ObjectBehavior
{
    /**
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $registry
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encodeFactory
     */
    function let($registry, $encodeFactory)
    {
        $this->beConstructedWith($registry, $encodeFactory);
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent $event
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $registry
     */
    function it_do_nothing_when_user_is_not_doctrine_entity($event, $registry)
    {
        $registry->getManagerForClass(
            'FSi\Bundle\AdminSecurityBundle\spec\fixtures\User'
        )->willReturn(null);
        $event->getUser()->shouldBeCalled()->willReturn(new User());

        $this->onChangePassword($event)->shouldReturn(null);
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent $event
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $registry
     * @param \FSi\Bundle\AdminSecurityBundle\spec\fixtures\User $user
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encodeFactory
     * @param \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface $encoder
     */
    function it_set_password_when_user_is_doctrine_entity($event, $registry, $user, $encodeFactory, $encoder)
    {
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
