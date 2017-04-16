<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;

class DeactivateUserListenerSpec extends ObjectBehavior
{
    function it_subscribes_deactivation_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            AdminSecurityEvents::DEACTIVATION => 'onDeactivation'
        ]);
    }

    function it_activates_user(ActivationEvent $event, ActivableInterface $user)
    {
        $event->getUser()->willReturn($user);

        $user->setEnabled(false)->shouldBeCalled();

        $this->onDeactivation($event);
    }
}
