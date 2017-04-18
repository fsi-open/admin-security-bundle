<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Doctrine\Admin;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserElementSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([], 'User\Model');
    }

    function it_has_id()
    {
        $this->getId()->shouldReturn('admin_security_user');
    }

    function it_return_correct_class_name()
    {
        $this->getClassName()->shouldReturn('User\Model');
    }
}
