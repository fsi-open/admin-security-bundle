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


}
