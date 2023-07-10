<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent;
use FSi\Bundle\AdminSecurityBundle\Event\ActivationTokenReset;
use FSi\Bundle\AdminSecurityBundle\Event\ActivationTokenResetEvent;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Event\DeactivationEvent;
use FSi\Bundle\AdminSecurityBundle\Event\DemoteUserEvent;
use FSi\Bundle\AdminSecurityBundle\Event\PromoteUserEvent;
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use FSi\Bundle\AdminSecurityBundle\Event\UserCreatedEvent;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

class PersistDoctrineUserListener implements EventSubscriberInterface
{
    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ChangePasswordEvent::class => 'onChangePassword',
            ResetPasswordRequestEvent::class => 'onResetPasswordRequest',
            ActivationEvent::class => 'onActivation',
            ActivationTokenResetEvent::class => 'onActivationResend',
            DeactivationEvent::class => 'onDeactivation',
            UserCreatedEvent::class => 'onUserCreated',
            PromoteUserEvent::class => 'onPromoteUser',
            DemoteUserEvent::class => 'onDemoteUser',
            InteractiveLoginEvent::class => 'onInteractiveLogin'
        ];
    }

    public function onActivation(ActivationEvent $event): void
    {
        $this->flushUserObjectManager($event->getUser());
    }

    public function onActivationResend(ActivationTokenResetEvent $event): void
    {
        $this->flushUserObjectManager($event->getUser());
    }

    public function onDeactivation(DeactivationEvent $event): void
    {
        $this->flushUserObjectManager($event->getUser());
    }

    public function onChangePassword(ChangePasswordEvent $event): void
    {
        $this->flushUserObjectManager($event->getUser());
    }

    public function onResetPasswordRequest(ResetPasswordRequestEvent $event): void
    {
        $this->flushUserObjectManager($event->getUser());
    }

    public function onUserCreated(UserCreatedEvent $event): void
    {
        $this->flushUserObjectManager($event->getUser());
    }

    public function onPromoteUser(PromoteUserEvent $event): void
    {
        $this->flushUserObjectManager($event->getUser());
    }

    public function onDemoteUser(DemoteUserEvent $event): void
    {
        $this->flushUserObjectManager($event->getUser());
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        if (false === is_object($user)) {
            throw new RuntimeException(sprintf(
                'Expected an object, got "%s" instead.',
                gettype($user)
            ));
        }

        $this->flushUserObjectManager($user);
    }

    private function flushUserObjectManager(object $user): void
    {
        $objectManager = $this->registry->getManagerForClass(get_class($user));
        if (false === $objectManager instanceof ObjectManager) {
            return;
        }

        if (false === $objectManager->contains($user)) {
            $objectManager->persist($user);
        }

        $objectManager->flush();
    }
}
