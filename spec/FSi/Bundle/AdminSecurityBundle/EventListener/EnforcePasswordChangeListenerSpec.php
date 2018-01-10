<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Security\Firewall\FirewallMapper;
use FSi\Bundle\AdminSecurityBundle\Security\User\EnforceablePasswordChangeInterface;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\FirewallConfig;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\FirewallMap as FixuresFirewallMap;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\LegacyFirewallMap;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EnforcePasswordChangeListenerSpec extends ObjectBehavior
{
    private const CONFIGURED_FIREWALL = 'firewall';
    private const USER_FIREWALL = 'user_firewall';

    function let(
        Request $request,
        GetResponseEvent $event,
        FirewallMapper $firewallMapper,
        FixuresFirewallMap $firewallMap,
        LegacyFirewallMap $legacyFirewallMap,
        FirewallConfig $firewallConfig,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        EnforceablePasswordChangeInterface $user,
        RouterInterface $router
    ) {
        $event->getRequest()->willReturn($request);
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);
        if (method_exists(FirewallMap::class, 'getFirewallConfig')) {
            $firewallMap->getFirewallConfig($request)->willReturn($firewallConfig);
            $firewallConfig->getName()->willReturn(self::CONFIGURED_FIREWALL);
        } else {
            $firewallMap = $legacyFirewallMap;
            $firewallMapper->getFirewallName($request)->willReturn(self::CONFIGURED_FIREWALL);
        }
        $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')->willReturn(true);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith(
            $firewallMapper,
            $firewallMap,
            $authorizationChecker,
            $tokenStorage,
            $router,
            self::CONFIGURED_FIREWALL,
            'change_password'
        );
    }

    function it_subscribes_user_created_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            KernelEvents::REQUEST => 'onKernelRequest'
        ]);
    }

    function it_does_nothing_if_not_master_request(
        GetResponseEvent $event,
        Request $request,
        TokenStorageInterface $tokenStorage,
        FirewallMapper $firewallMapper
    ) {
        $event->getRequestType()->willReturn(HttpKernelInterface::SUB_REQUEST);

        $tokenStorage->getToken()->shouldNotBeCalled();
        $firewallMapper->getFirewallName($request)->shouldNotBeCalled();
        $event->setResponse(Argument::any())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_nothing_if_there_is_no_current_firewall(
        GetResponseEvent $event,
        Request $request,
        FirewallConfig $firewallConfig,
        FirewallMapper $firewallMapper
    ) {
        if (method_exists(FirewallMap::class, 'getFirewallConfig')) {
            $firewallConfig->getName()->willReturn(null);
        } else {
            $firewallMapper->getFirewallName($request)->willReturn(null);
        }

        $event->setResponse(Argument::any())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_nothing_if_there_is_no_token(
        GetResponseEvent $event,
        TokenStorageInterface $tokenStorage
    ) {
        $tokenStorage->getToken()->willReturn(null);

        $event->setResponse(Argument::any())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_nothing_if_there_current_firewall_is_not_configured_in_this_listener(
        GetResponseEvent $event,
        Request $request,
        FirewallConfig $firewallConfig,
        FirewallMapper $firewallMapper
    ) {
        if (method_exists(FirewallMap::class, 'getFirewallConfig')) {
            $firewallConfig->getName()->willReturn(self::USER_FIREWALL);
        } else {
            $firewallMapper->getFirewallName($request)->willReturn(self::USER_FIREWALL);
        }

        $event->setResponse(Argument::any())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_nothing_when_user_is_not_logged_in(
        GetResponseEvent $event,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')->willReturn(false);

        $event->setResponse(Argument::any())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_nothing_when_user_has_not_enforce_password_change(
        GetResponseEvent $event,
        EnforceablePasswordChangeInterface $user
    ) {
        $user->isForcedToChangePassword()->willReturn(false);

        $event->setResponse(Argument::any())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_stops_event_propagation_when_already_on_change_password_page(
        GetResponseEvent $event,
        Request $request,
        EnforceablePasswordChangeInterface $user
    ) {
        $user->isForcedToChangePassword()->willReturn(true);
        $request->get('_route')->willReturn('change_password');

        $event->stopPropagation()->shouldBeCalled();
        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_redirects_to_change_password(
        GetResponseEvent $event,
        Request $request,
        RouterInterface $router,
        EnforceablePasswordChangeInterface $user
    ) {
        $user->isForcedToChangePassword()->willReturn(true);
        $request->get('_route')->willReturn('something_secure');
        $router->generate('change_password')->willReturn('change_password_url');

        $event->setResponse(Argument::allOf(
            Argument::type(RedirectResponse::class),
            Argument::which('getTargetUrl', 'change_password_url')
        ))->shouldBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }
}
