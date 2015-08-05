<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
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

        $doctrineConfigDir = realpath(__DIR__ . '/Resources/config/doctrine');

        $mappings = array(
            $doctrineConfigDir . '/User' => 'FSi\Bundle\AdminSecurityBundle\Security\User',
            $doctrineConfigDir . '/Token' => 'FSi\Bundle\AdminSecurityBundle\Security\Token',
        );

        $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings));

        if ($container->hasExtension('fos_user')) {
            $mappings = array(
                $doctrineConfigDir . '/FOS' => 'FSi\Bundle\AdminSecurityBundle\Security\FOS',
            );

            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings));
        }
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
