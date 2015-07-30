<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Security\Firewall;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FirewallMapperSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Component\HttpFoundation\RequestMatcherInterface $matcher1
     * @param \Symfony\Component\HttpFoundation\RequestMatcherInterface $matcher2
     * @param \Symfony\Component\HttpFoundation\RequestMatcherInterface $matcher3
     */
    function let($matcher1, $matcher2, $matcher3)
    {
        $this->beConstructedWith(array(
            'firewall1' => $matcher1,
            'firewall2' => $matcher2,
            'firewall3' => $matcher3
        ));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\RequestMatcherInterface $matcher1
     * @param \Symfony\Component\HttpFoundation\RequestMatcherInterface $matcher2
     * @param \Symfony\Component\HttpFoundation\RequestMatcherInterface $matcher3
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    function it_returns_firewall_name_for_first_matching_request($matcher1, $matcher2, $matcher3, $request)
    {
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
