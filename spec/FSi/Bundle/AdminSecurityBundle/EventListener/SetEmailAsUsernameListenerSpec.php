<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SetEmailAsUsernameListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('FSi\Bundle\AdminSecurityBundle\EventListener\SetEmailAsUsernameListener');
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\UserEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     */
    function it_should_set_email_as_username($event, $user)
    {
        $event->getUser()->willReturn($user);

        $user->getEmail()->willReturn('test@example.com');
        $user->setUsername('test@example.com')->shouldBeCalled();

        $this->setEmailAsUsername($event);
    }
}
