<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\DependencyInjection;

use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Norbert Orzechowicz <norbert@fsi.pl>
 */
class FSIAdminSecurityExtensionSpec extends ObjectBehavior
{
    function it_is_extension()
    {
        $this->shouldHaveType('Symfony\Component\HttpKernel\DependencyInjection\Extension');
    }
}
