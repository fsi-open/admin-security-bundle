<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordTokenSetEvent;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SetPasswordResetTokenListener implements EventSubscriberInterface
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
        return [
            ResetPasswordRequestEvent::class => 'setPasswordResetToken'
        ];
    }

    public function setPasswordResetToken(ResetPasswordRequestEvent $event): void
    {
        $user = $event->getUser();
        $user->setPasswordResetToken($this->tokenFactory->createToken());

        $this->eventDispatcher->dispatch(new ResetPasswordTokenSetEvent($user));
    }
}
