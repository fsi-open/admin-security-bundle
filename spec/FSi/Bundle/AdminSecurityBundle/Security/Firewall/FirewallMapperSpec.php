<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\Security\Firewall;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class FirewallMapperSpec extends ObjectBehavior
{
    function let(
        RequestMatcherInterface $matcher1,
        RequestMatcherInterface $matcher2,
        RequestMatcherInterface $matcher3
    ) {
        $this->beConstructedWith([
            'firewall1' => $matcher1,
            'firewall2' => $matcher2,
            'firewall3' => $matcher3
        ]);
    }

    function it_returns_firewall_name_for_first_matching_request(
        RequestMatcherInterface $matcher1,
        RequestMatcherInterface $matcher2,
        RequestMatcherInterface $matcher3,
        Request $request
    ) {
        $matcher1->matches($request)->willReturn(false);
        $matcher2->matches($request)->willReturn(false);
        $matcher3->matches($request)->willReturn(true);

        $this->getFirewallName($request)->shouldReturn('firewall3');

        $matcher1->matches($request)->willReturn(false);
        $matcher2->matches($request)->willReturn(true);
        $matcher3->matches($request)->willReturn(true);

        $this->getFirewallName($request)->shouldReturn('firewall2');

        $matcher1->matches($request)->willReturn(true);
        $matcher2->matches($request)->willReturn(false);
        $matcher3->matches($request)->willReturn(true);

        $this->getFirewallName($request)->shouldReturn('firewall1');
    }
}
