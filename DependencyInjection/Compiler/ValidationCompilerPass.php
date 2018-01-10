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

class ValidationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('admin_security.storage')) {
            return;
        }

        if (!$container->hasDefinition('validator.builder')) {
            return;
        }

        $storage = $container->getParameter('admin_security.storage');

        $validationFile = __DIR__ . '/../../Resources/config/storage-validation/' . $storage . '.xml';

        $container->getDefinition('validator.builder')
            ->addMethodCall('addXmlMapping', [$validationFile]);
    }
}
