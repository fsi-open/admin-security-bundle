<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Security\User\EnforceablePasswordChangeInterface;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\FirewallMapInterface;

use function get_class;
use function method_exists;

class EnforcePasswordChangeListener implements EventSubscriberInterface
{
    /**
     * @var FirewallMapInterface
     */
    private $firewallMap;

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

    public function __construct(
        FirewallMapInterface $firewallMap,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        string $firewallName,
        string $changePasswordRoute
    ) {
        $this->firewallMap = $firewallMap;
        $this->authorizationChecker = $authorizationChecker;
        $this->firewallName = $firewallName;
        $this->changePasswordRoute = $changePasswordRoute;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return;
        }

        if (false === $this->isConfiguredFirewall($event->getRequest())) {
            return;
        }

        if (false === $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return;
        }

        if (true === $this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            return;
        }

        $user = $token->getUser();
        if (
            false === $user instanceof EnforceablePasswordChangeInterface
            || false === $user->isForcedToChangePassword()
        ) {
            return;
        }

        $this->redirectToChangePassword($event);
    }

    private function isConfiguredFirewall(Request $request): bool
    {
        if (false === method_exists($this->firewallMap, 'getFirewallConfig')) {
            throw new RuntimeException(
                sprintf(
                    "Got instance of %s which is an incompatible implementation of %s, expected i.e instance of %s",
                    get_class($this->firewallMap),
                    FirewallMapInterface::class,
                    FirewallMap::class
                )
            );
        }

        $config = $this->firewallMap->getFirewallConfig($request);
        if (null === $config) {
            return false;
        }

        return $config->getName() === $this->firewallName;
    }

    private function redirectToChangePassword(RequestEvent $event): void
    {
        if ($event->getRequest()->get('_route') !== $this->changePasswordRoute) {
            $event->setResponse(new RedirectResponse($this->router->generate($this->changePasswordRoute)));
        } else {
            $event->stopPropagation();
        }
    }
}
