<?php

namespace spec\FSi\Bundle\AdminSecurityBundle;

use PhpSpec\ObjectBehavior;

class FSiAdminSecurityBundleSpec extends ObjectBehavior
{
    function it_is_bundle()
    {
        $this->shouldHaveType('Symfony\Component\HttpKernel\Bundle\Bundle');
    }

    function it_have_custom_extension()
    {
        $this->getContainerExtension()
            ->shouldReturnAnInstanceOf('FSi\Bundle\AdminSecurityBundle\DependencyInjection\FSIAdminSecurityExtension');
    }
}
