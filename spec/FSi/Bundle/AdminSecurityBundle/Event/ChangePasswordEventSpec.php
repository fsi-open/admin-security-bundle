<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Event;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\User\UserInterface;

class ChangePasswordEventSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     */
    function let($user)
    {
        $this->beConstructedWith($user, 'plain_password');
    }

    function it_is_event()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\Event');
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     */
    function it_returns_user($user)
    {
        $this->getUser()->shouldReturn($user);
    }

    function it_returns_plain_password()
    {
        $this->getPlainPassword()->shouldReturn('plain_password');
    }
}
