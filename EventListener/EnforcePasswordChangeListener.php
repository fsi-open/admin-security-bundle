<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Security\Firewall\FirewallMapper;
use FSi\Bundle\AdminSecurityBundle\Security\User\EnforceablePasswordChangeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EnforcePasswordChangeListener implements EventSubscriberInterface
{
    /**
     * @var FirewallMapper
     */
    private $firewallMapper;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var string
     */
    private $firewallName;

    /**
     * @var string
     */
    private $changePasswordRoute;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param FirewallMapper $firewallMapper
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface $tokenStorage
     * @param RouterInterface $router
     * @param string $firewallName
     * @param string $changePasswordRoute
     */
    public function __construct(
        FirewallMapper $firewallMapper,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        $firewallName,
        $changePasswordRoute
    ) {
        $this->firewallMapper = $firewallMapper;
        $this->authorizationChecker = $authorizationChecker;
        $this->firewallName = $firewallName;
        $this->changePasswordRoute = $changePasswordRoute;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest'
        );
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return;
        }

        $firewallName = $this->firewallMapper->getFirewallName($event->getRequest());
        if (empty($firewallName) || ($firewallName !== $this->firewallName)) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return;
        }

        $user = $token->getUser();
        if (!($user instanceof EnforceablePasswordChangeInterface) ||
            !$user->isForcedToChangePassword()) {
            return;
        }

        if ($event->getRequest()->get('_route') !== $this->changePasswordRoute) {
            $event->setResponse(new RedirectResponse($this->router->generate($this->changePasswordRoute)));
        } else {
            $event->stopPropagation();
        }
    }
}
