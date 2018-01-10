<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use FSi\Bundle\AdminSecurityBundle\DependencyInjection\Compiler\FirewallMapCompilerPass;
use FSi\Bundle\AdminSecurityBundle\DependencyInjection\Compiler\ValidationCompilerPass;
use FSi\Bundle\AdminSecurityBundle\DependencyInjection\FSIAdminSecurityExtension;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FSiAdminSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new FirewallMapCompilerPass());
        $container->addCompilerPass(new ValidationCompilerPass());

        $doctrineConfigDir = realpath(__DIR__ . '/Resources/config/doctrine');

        $mappings = [
            $doctrineConfigDir . '/User' => 'FSi\Bundle\AdminSecurityBundle\Security\User',
            $doctrineConfigDir . '/Token' => 'FSi\Bundle\AdminSecurityBundle\Security\Token',
        ];

        $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings));
    }

    public function boot(): void
    {
        $userRepository = $this->container->get('admin_security.repository.user');
        if (!($userRepository instanceof UserRepositoryInterface)) {
            throw new LogicException(sprintf(
                'Repository for class "\%s" does not implement the "\%s" interface!',
                get_class($userRepository),
                'FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface'
            ));
        }

        parent::boot();
    }

    public function getContainerExtension(): FSIAdminSecurityExtension
    {
        if (null === $this->extension) {
            $this->extension = new FSIAdminSecurityExtension();
        }

        return $this->extension;
    }
}
