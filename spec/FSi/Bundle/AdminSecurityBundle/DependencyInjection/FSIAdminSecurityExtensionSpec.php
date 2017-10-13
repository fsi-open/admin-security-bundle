<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\DependencyInjection;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class FSIAdminSecurityExtensionSpec extends ObjectBehavior
{
    function it_is_extension()
    {
        $this->shouldHaveType(Extension::class);
    }
}
