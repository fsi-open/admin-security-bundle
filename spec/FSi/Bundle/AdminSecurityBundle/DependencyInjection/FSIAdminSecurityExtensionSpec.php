<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\DependencyInjection;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
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

    function it_can_prepend_other_extension_configuration()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface');
    }

    function it_prepend_admin_bundle_configuration(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('fsi_admin', array(
            'templates' => array(
                'base' => 'FSiAdminSecurityBundle:Admin:base.html.twig'
            )
        ))->shouldBeCalled();

        $this->prepend($container);
    }
}