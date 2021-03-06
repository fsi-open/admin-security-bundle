<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\spec\fixtures;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\FirewallMapInterface;

class FirewallMap implements FirewallMapInterface
{
    public function getListeners(Request $request): array
    {
        return [];
    }

    public function getFirewallConfig(Request $request): ?FirewallConfig
    {
        return null;
    }
}
