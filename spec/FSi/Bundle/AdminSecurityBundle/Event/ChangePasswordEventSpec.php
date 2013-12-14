<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Event;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\User\UserInterface;

class ChangePasswordEventSpec extends ObjectBehavior
{
    function let(UserInterface $user)
    {
        $this->beConstructedWith($user, 'plain_password');
    }

    function it_is_event()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\Event');
    }

    function it_returns_user(UserInterface $user)
    {
        $this->getUser()->shouldReturn($user);
    }

    function it_returns_plain_password()
    {
        $this->getPlainPassword()->shouldReturn('plain_password');
    }
}
