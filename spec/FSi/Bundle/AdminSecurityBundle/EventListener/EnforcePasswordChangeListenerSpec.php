<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\KernelEvents;

class EnforcePasswordChangeListenerSpec extends ObjectBehavior
{
    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Firewall\FirewallMapper $firewallMapper
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    function let($firewallMapper, $authorizationChecker, $tokenStorage, $router)
    {
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
        $this->getSubscribedEvents()->shouldReturn(array(
            KernelEvents::REQUEST => 'onKernelRequest'
        ));
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Firewall\FirewallMapper $firewallMapper
     */
    function it_does_nothing_if_there_is_no_current_firewall($event, $request, $firewallMapper)
    {
        $event->getRequest()->willReturn($request);
        $firewallMapper->getFirewallName($request)->willReturn(null);

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Firewall\FirewallMapper $firewallMapper
     */
    function it_does_nothing_if_there_current_firewall_is_not_configured_in_this_listener(
        $event, $request, $firewallMapper
    ) {
        $event->getRequest()->willReturn($request);
        $firewallMapper->getFirewallName($request)->willReturn('user_firewall');

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Firewall\FirewallMapper $firewallMapper
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     */
    function it_does_nothing_when_user_is_not_logged_in(
        $event, $request, $firewallMapper, $authorizationChecker
    ) {
        $event->getRequest()->willReturn($request);
        $firewallMapper->getFirewallName($request)->willReturn('firewall');
        $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')->willReturn(false);

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Firewall\FirewallMapper $firewallMapper
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Model\EnforceablePasswordChangeInterface $user
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    function it_does_nothing_when_user_has_not_enforce_password_change(
        $event, $request, $firewallMapper, $authorizationChecker, $tokenStorage, $token, $user, $router
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

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Firewall\FirewallMapper $firewallMapper
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Model\EnforceablePasswordChangeInterface $user
     */
    function it_does_nothing_when_current_route_is_for_changing_password(
        $event, $request, $firewallMapper, $authorizationChecker, $tokenStorage, $token, $user
    ) {
        $event->getRequest()->willReturn($request);
        $firewallMapper->getFirewallName($request)->willReturn('firewall');
        $authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')->willReturn(true);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->isForcedToChangePassword()->willReturn(true);
        $request->get('_route')->willReturn('change_password');

        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Firewall\FirewallMapper $firewallMapper
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Model\EnforceablePasswordChangeInterface $user
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    function it_redirects_to_change_password(
        $event, $request, $firewallMapper, $authorizationChecker, $tokenStorage, $token, $user, $router
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
