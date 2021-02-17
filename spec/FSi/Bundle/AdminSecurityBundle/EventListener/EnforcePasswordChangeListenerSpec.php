<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Security\User\EnforceablePasswordChangeInterface;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\FirewallConfig;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\FirewallMap as FixturesFirewallMap;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
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

    public function let(
        Request $request,
        RequestEvent $event,
        FixturesFirewallMap $firewallMap,
        FirewallConfig $firewallConfig,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        EnforceablePasswordChangeInterface $user,
        RouterInterface $router
    ): void {
        $event->getRequest()->willReturn($request);
        $event->getRequestType()->willReturn(HttpKernelInterface::MASTER_REQUEST);
        $firewallMap->getFirewallConfig($request)->willReturn($firewallConfig);
        $firewallConfig->getName()->willReturn(self::CONFIGURED_FIREWALL);
        $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')->willReturn(true);
        $authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')->willReturn(false);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith(
            $firewallMap,
            $authorizationChecker,
            $tokenStorage,
            $router,
            self::CONFIGURED_FIREWALL,
            'change_password'
        );
    }

    public function it_subscribes_user_created_event(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            KernelEvents::REQUEST => 'onKernelRequest',
        ]);
    }

    public function it_does_nothing_if_not_master_request(
        RequestEvent $event,
        TokenStorageInterface $tokenStorage
    ): void {
        $event->getRequestType()->willReturn(HttpKernelInterface::SUB_REQUEST);

        $tokenStorage->getToken()->shouldNotBeCalled();
        $event->setResponse(Argument::any())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    public function it_does_nothing_if_there_is_no_current_firewall(
        RequestEvent $event,
        FirewallMap $firewallMap,
        Request $request
    ): void {
        $firewallMap->getFirewallConfig($request)->willReturn(null);

        $event->setResponse(Argument::any())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    public function it_does_nothing_if_there_is_no_token(
        RequestEvent $event,
        TokenStorageInterface $tokenStorage
    ): void {
        $tokenStorage->getToken()->willReturn(null);

        $event->setResponse(Argument::any())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    public function it_does_nothing_if_there_current_firewall_is_not_configured_in_this_listener(
        RequestEvent $event,
        FirewallConfig $firewallConfig
    ): void {
        $firewallConfig->getName()->willReturn(self::USER_FIREWALL);

        $event->setResponse(Argument::any())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    public function it_does_nothing_when_user_is_not_logged_in(
        RequestEvent $event,
        AuthorizationCheckerInterface $authorizationChecker
    ): void {
        $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')->willReturn(false);

        $event->setResponse(Argument::any())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    public function it_does_nothing_when_user_is_impersonated(
        RequestEvent $event,
        AuthorizationCheckerInterface $authorizationChecker
    ): void {
        $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')->willReturn(true);
        $authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')->willReturn(true);

        $event->setResponse(Argument::any())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    public function it_does_nothing_when_user_has_not_enforce_password_change(
        RequestEvent $event,
        EnforceablePasswordChangeInterface $user
    ): void {
        $user->isForcedToChangePassword()->willReturn(false);

        $event->setResponse(Argument::any())->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    public function it_stops_event_propagation_when_already_on_change_password_page(
        RequestEvent $event,
        Request $request,
        EnforceablePasswordChangeInterface $user
    ): void {
        $user->isForcedToChangePassword()->willReturn(true);
        $request->get('_route')->willReturn('change_password');

        $event->stopPropagation()->shouldBeCalled();
        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    public function it_redirects_to_change_password(
        RequestEvent $event,
        Request $request,
        RouterInterface $router,
        EnforceablePasswordChangeInterface $user
    ): void {
        $user->isForcedToChangePassword()->willReturn(true);
        $request->get('_route')->willReturn('something_secure');
        $router->generate('change_password')->willReturn('change_password_url');

        $event->setResponse(
            Argument::allOf(
                Argument::type(RedirectResponse::class),
                Argument::which('getTargetUrl', 'change_password_url')
            )
        )->shouldBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }
}
