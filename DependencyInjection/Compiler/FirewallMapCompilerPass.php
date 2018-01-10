<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FirewallMapCompilerPass implements CompilerPassInterface
{
    private const FIREWALL_MAPPER_SERVICE = 'admin_security.firewall_mapper';

    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasAlias(self::FIREWALL_MAPPER_SERVICE) &&
            false === $container->hasDefinition(self::FIREWALL_MAPPER_SERVICE)) {
            return;
        }

        $map = $container->getDefinition('security.firewall.map');
        $maps = $map->getArgument(1);

        $refs = [];
        foreach ($maps as $serviceName => $firewall) {
            $refs[substr($serviceName, 30)] = $firewall;
        }

        $firewallManagerDef = $container->getDefinition(self::FIREWALL_MAPPER_SERVICE);
        $firewallManagerDef->replaceArgument(0, $refs);
    }
}
