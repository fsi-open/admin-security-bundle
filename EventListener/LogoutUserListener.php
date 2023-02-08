<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogoutUserListener implements EventSubscriberInterface
{
    private RequestStack $requestStack;
    private TokenStorageInterface $tokenStorage;

    public function __construct(RequestStack $requestStack, TokenStorageInterface $tokenStorage)
    {
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ChangePasswordEvent::class => 'onChangePassword'
        ];
    }

    public function onChangePassword(ChangePasswordEvent $event): void
    {
        $token = $this->tokenStorage->getToken();
        if (null !== $token && $token->getUser() === $event->getUser()) {
            $request = $this->requestStack->getMasterRequest();
            if (null === $request) {
                throw new RuntimeException('No request when attempting to log out the user!');
            }

            $request->getSession()->invalidate();
            $this->tokenStorage->setToken(null);
        }
    }
}
