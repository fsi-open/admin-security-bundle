<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\spec\fixtures\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DoctrineUserListenerSpec extends ObjectBehavior
{
    /**
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $registry
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    function let($registry, $objectManager)
    {
        $this->beConstructedWith($registry);
        $registry->getManagerForClass(Argument::any())->willReturn($objectManager);
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent $event
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    function it_flushes_om_after_changing_password($event, $objectManager)
    {
        $event->getUser()->willReturn(new User());

        $objectManager->flush()->shouldBeCalled();

        $this->onChangePassword($event);
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent $event
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    function it_flushes_om_after_requesting_change_of_password($event, $objectManager)
    {
        $event->getUser()->willReturn(new User());

        $objectManager->flush()->shouldBeCalled();

        $this->onResetPasswordRequest($event);
    }
}
