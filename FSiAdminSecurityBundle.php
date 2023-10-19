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
use FSi\Bundle\AdminSecurityBundle\DependencyInjection\Compiler\EnforceSecuredElementsCompilerPass;
use FSi\Bundle\AdminSecurityBundle\DependencyInjection\Compiler\MailerCompilerPass;
use FSi\Bundle\AdminSecurityBundle\DependencyInjection\Compiler\PasswordEncodingPass;
use FSi\Bundle\AdminSecurityBundle\DependencyInjection\Compiler\ValidationCompilerPass;
use FSi\Bundle\AdminSecurityBundle\DependencyInjection\FSIAdminSecurityExtension;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use function get_class;
use function gettype;
use function is_object;

class FSiAdminSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new EnforceSecuredElementsCompilerPass());
        $container->addCompilerPass(new MailerCompilerPass());
        $container->addCompilerPass(new PasswordEncodingPass());
        $container->addCompilerPass(new ValidationCompilerPass());

        $doctrineConfigDir = realpath(__DIR__ . '/Resources/config/doctrine');
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createXmlMappingDriver([
                "{$doctrineConfigDir}/User" => 'FSi\Bundle\AdminSecurityBundle\Security\User',
                "{$doctrineConfigDir}/Token" => 'FSi\Bundle\AdminSecurityBundle\Security\Token',
            ])
        );
    }

    public function boot(): void
    {
        $userRepository = $this->container->get('admin_security.repository.user');
        if (false === $userRepository instanceof UserRepositoryInterface) {
            throw new LogicException(sprintf(
                'Repository for class "%s" does not implement the "%s" interface!',
                true === is_object($userRepository)
                    ? get_class($userRepository)
                    : gettype($userRepository),
                UserRepositoryInterface::class
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
