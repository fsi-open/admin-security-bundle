<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\DependencyInjection;

use PhpSpec\ObjectBehavior;

class FSIAdminSecurityExtensionSpec extends ObjectBehavior
{
    function it_is_extension()
    {
        $this->shouldHaveType('Symfony\Component\HttpKernel\DependencyInjection\Extension');
    }
}
