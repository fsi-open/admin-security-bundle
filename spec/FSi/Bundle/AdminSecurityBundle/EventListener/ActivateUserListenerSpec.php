<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ActivateUserListenerSpec extends ObjectBehavior
{
    function it_subscribes_activation_event()
    {
        $this->getSubscribedEvents()->shouldReturn(array(
            AdminSecurityEvents::ACTIVATION => 'onActivation'
        ));
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Model\ActivableInterface $user
     */
    function it_activates_user($event, $user)
    {
        $event->getUser()->willReturn($user);

        $user->setEnabled(true)->shouldBeCalled();
        $user->removeActivationToken()->shouldBeCalled();

        $this->onActivation($event);
    }
}
