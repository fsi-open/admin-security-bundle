<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeactivateUserListenerSpec extends ObjectBehavior
{
    function it_subscribes_deactivation_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            AdminSecurityEvents::DEACTIVATION => 'onDeactivation'
        ]);
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface $user
     */
    function it_activates_user($event, $user)
    {
        $event->getUser()->willReturn($user);

        $user->setEnabled(false)->shouldBeCalled();

        $this->onDeactivation($event);
    }
}
