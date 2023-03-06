<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

use function interface_exists;

class PasswordEncodingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (true === interface_exists(PasswordHasherFactoryInterface::class)) {
            $container->removeDefinition('admin_security.listener.legacy_encode_password');
        } else {
            $container->removeDefinition('admin_security.listener.encode_password');
        }
    }
}
