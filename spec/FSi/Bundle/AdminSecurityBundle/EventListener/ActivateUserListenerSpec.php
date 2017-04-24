<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use PhpSpec\ObjectBehavior;

class ActivateUserListenerSpec extends ObjectBehavior
{
    function it_subscribes_activation_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            AdminSecurityEvents::ACTIVATION => 'onActivation'
        ]);
    }

    function it_activates_user(ActivationEvent $event, ActivableInterface $user)
    {
        $event->getUser()->willReturn($user);

        $user->setEnabled(true)->shouldBeCalled();
        $user->removeActivationToken()->shouldBeCalled();

        $this->onActivation($event);
    }
}
