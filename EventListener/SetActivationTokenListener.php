<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\ActivationTokenSetEvent;
use FSi\Bundle\AdminSecurityBundle\Event\UserCreatedEvent;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SetActivationTokenListener implements EventSubscriberInterface
{
    private TokenFactoryInterface $tokenFactory;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        TokenFactoryInterface $tokenFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [UserCreatedEvent::class => 'onUserCreated'];
    }

    public function onUserCreated(UserCreatedEvent $event): void
    {
        $user = $event->getUser();
        if (false === $user instanceof ActivableInterface || true === $user->isEnabled()) {
            return;
        }

        $user->setActivationToken($this->tokenFactory->createToken());
        $this->eventDispatcher->dispatch(new ActivationTokenSetEvent($user));
    }
}
