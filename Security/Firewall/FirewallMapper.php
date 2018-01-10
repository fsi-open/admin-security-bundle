<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class FirewallMapper
{
    /**
     * @var RequestMatcherInterface[]
     */
    private $map;

    /**
     * @param RequestMatcherInterface[] $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function getFirewallName(Request $request): ?string
    {
        foreach ($this->map as $firewallName => $requestMatcher) {
            if (null === $requestMatcher || $requestMatcher->matches($request)) {
                return $firewallName;
            }
        }

        return null;
    }
}
