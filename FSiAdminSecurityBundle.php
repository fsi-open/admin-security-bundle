<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle;

use FSi\Bundle\AdminSecurityBundle\DependencyInjection\Compiler\FirewallMapCompilerPass;
use FSi\Bundle\AdminSecurityBundle\DependencyInjection\FSIAdminSecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FSiAdminSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FirewallMapCompilerPass());
    }

    /**
     * @return FSIAdminSecurityExtension
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new FSIAdminSecurityExtension();
        }

        return $this->extension;
    }
}
