<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Admin;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserElementSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(array(), 'User\Model');
    }
}
