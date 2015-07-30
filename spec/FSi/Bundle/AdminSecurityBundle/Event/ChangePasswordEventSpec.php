<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Event;

use PhpSpec\ObjectBehavior;

class ChangePasswordEventSpec extends ObjectBehavior
{
    /**
     * @param FSi\Bundle\AdminSecurityBundle\Security\User\UserPasswordChangeInterface $user
     */
    function let($user)
    {
        $this->beConstructedWith($user);
    }

    function it_is_event()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\Event');
    }

    /**
     * @param FSi\Bundle\AdminSecurityBundle\Security\User\UserPasswordChangeInterface $user
     */
    function it_returns_user($user)
    {
        $this->getUser()->shouldReturn($user);
    }
}
