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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogoutUserListener implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(RequestStack $requestStack, TokenStorageInterface $tokenStorage)
    {
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AdminSecurityEvents::CHANGE_PASSWORD => 'onChangePassword'
        ];
    }

    public function onChangePassword(ChangePasswordEvent $event): void
    {
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser() === $event->getUser()) {
            $this->requestStack->getMasterRequest()->getSession()->invalidate();
            $this->tokenStorage->setToken(null);
        }
    }
}
