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
use FSi\Bundle\AdminSecurityBundle\DependencyInjection\Compiler\ValidationCompilerPass;
use FSi\Bundle\AdminSecurityBundle\DependencyInjection\FSIAdminSecurityExtension;
use LogicException;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FSiAdminSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
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

        if ($container->hasExtension('fos_user')) {
            $mappings = [
                $doctrineConfigDir . '/FOS' => 'FSi\Bundle\AdminSecurityBundle\Security\FOS',
            ];

            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings));
        }
    }

    public function boot()
    {
        /** @var RegistryInterface $doctrine */
        $doctrine = $this->container->get('doctrine');
        $userClass = $this->container->getParameter('admin_security.model.user');
        $userRepositoryClass = 'FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface';
        $repositoryClass = $doctrine->getManagerForClass($userClass)
            ->getClassMetadata($userClass)
            ->customRepositoryClassName
        ;
        if (!is_subclass_of($repositoryClass, $userRepositoryClass)) {
            throw new LogicException(sprintf(
                'Repository for class "\%s" does not implement the "\%s" interface!',
                    $userClass,
                    $userRepositoryClass
            ));
        }

        parent::boot();
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
