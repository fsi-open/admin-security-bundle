<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Event;

use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\Event;

class ChangePasswordEventSpec extends ObjectBehavior
{
    function let(ChangeablePasswordInterface $user)
    {
        $this->beConstructedWith($user);
    }

    function it_is_event()
    {
        $this->shouldHaveType(Event::class);
    }

    function it_returns_user(ChangeablePasswordInterface $user)
    {
        $this->getUser()->shouldReturn($user);
    }
}
