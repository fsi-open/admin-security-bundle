<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use PhpSpec\ObjectBehavior;

class SetEmailAsUsernameListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('FSi\Bundle\AdminSecurityBundle\EventListener\SetEmailAsUsernameListener');
    }

    function it_should_set_email_as_username(UserEvent $event, UserInterface $user)
    {
        $event->getUser()->willReturn($user);

        $user->getEmail()->willReturn('test@example.com');
        $user->setUsername('test@example.com')->shouldBeCalled();

        $this->setEmailAsUsername($event);
    }
}
