<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Security\Firewall\FirewallMapper;
use FSi\Bundle\AdminSecurityBundle\Security\User\EnforceablePasswordChangeInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\RouterInterface;

class EnforcePasswordChangeListenerSpec extends ObjectBehavior
{
    function let(
        FirewallMapper $firewallMapper,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router
    ) {
        $this->beConstructedWith(
            $firewallMapper,
            $authorizationChecker,
            $tokenStorage,
            $router,
            'firewall',
            'change_password'
        );
    }

    function it_subscribes_user_created_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            KernelEvents::REQUEST => 'onKernelRequest'
        ]);
    }

    function it_does_nothing_if_there_is_no_current_firewall(
        GetResponseEvent $event,
        Request $request,
        FirewallMapper $firewallMapper
    ) {
        $event->getRequest()->willReturn($request);
        $firewallMapper->getFirewallName($request)->willReturn(null);

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_nothing_if_there_is_no_token(
        GetResponseEvent $event,
        TokenStorageInterface $tokenStorage
    ) {
        $tokenStorage->getToken()->willReturn(null);

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_nothing_if_there_current_firewall_is_not_configured_in_this_listener(
        GetResponseEvent $event,
        Request $request,
        FirewallMapper $firewallMapper
    ) {
        $event->getRequest()->willReturn($request);
        $firewallMapper->getFirewallName($request)->willReturn('user_firewall');

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_nothing_when_user_is_not_logged_in(
        GetResponseEvent $event,
        Request $request,
        FirewallMapper $firewallMapper,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $event->getRequest()->willReturn($request);
        $firewallMapper->getFirewallName($request)->willReturn('firewall');
        $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')->willReturn(false);

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_nothing_when_user_has_not_enforce_password_change(
        GetResponseEvent $event,
        Request $request,
        FirewallMapper $firewallMapper,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        EnforceablePasswordChangeInterface $user
    ) {
        $event->getRequest()->willReturn($request);
        $firewallMapper->getFirewallName($request)->willReturn('firewall');
        $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')->willReturn(true);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->isForcedToChangePassword()->willReturn(false);

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_stops_event_propagation_when_already_on_change_password_page(
        GetResponseEvent $event,
        Request $request,
        FirewallMapper $firewallMapper,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        EnforceablePasswordChangeInterface $user
    ) {
        $event->getRequest()->willReturn($request);
        $firewallMapper->getFirewallName($request)->willReturn('firewall');
        $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')->willReturn(true);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->isForcedToChangePassword()->willReturn(true);
        $request->get('_route')->willReturn('change_password');

        $event->stopPropagation()->shouldBeCalled();
        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_redirects_to_change_password(
        GetResponseEvent $event,
        Request $request,
        FirewallMapper $firewallMapper,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        EnforceablePasswordChangeInterface $user,
        RouterInterface $router
    ) {
        $event->getRequest()->willReturn($request);
        $firewallMapper->getFirewallName($request)->willReturn('firewall');
        $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')->willReturn(true);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->isForcedToChangePassword()->willReturn(true);
        $request->get('_route')->willReturn('something_secure');
        $router->generate('change_password')->willReturn('change_password_url');

        $event->setResponse(Argument::allOf(
            Argument::type('\Symfony\Component\HttpFoundation\RedirectResponse'),
            Argument::which('getTargetUrl', 'change_password_url')
        ))->shouldBeCalled();

        $this->onKernelRequest($event);
    }
}
