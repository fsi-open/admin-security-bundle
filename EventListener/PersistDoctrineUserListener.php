<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class PersistDoctrineUserListener implements EventSubscriberInterface
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AdminSecurityEvents::CHANGE_PASSWORD => 'onChangePassword',
            AdminSecurityEvents::RESET_PASSWORD_REQUEST => 'onResetPasswordRequest',
            AdminSecurityEvents::ACTIVATION => 'onActivation',
            AdminSecurityEvents::DEACTIVATION => 'onDeactivation',
            AdminSecurityEvents::USER_CREATED => 'onUserCreated',
            AdminSecurityEvents::PROMOTE_USER => 'onPromoteUser',
            AdminSecurityEvents::DEMOTE_USER => 'onDemoteUser',
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin'
        ];
    }

    public function onActivation(ActivationEvent $event): void
    {
        $this->flushUserObjectManager($event->getUser());
    }

    public function onDeactivation(ActivationEvent $event): void
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

    public function onUserCreated(UserEvent $event): void
    {
        $this->flushUserObjectManager($event->getUser());
    }

    public function onPromoteUser(UserEvent $event): void
    {
        $this->flushUserObjectManager($event->getUser());
    }

    public function onDemoteUser(UserEvent $event): void
    {
        $this->flushUserObjectManager($event->getUser());
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $this->flushUserObjectManager($event->getAuthenticationToken()->getUser());
    }

    /**
     * @param object $user
     */
    private function flushUserObjectManager($user): void
    {
        $objectManager = $this->registry->getManagerForClass(get_class($user));

        if ($objectManager instanceof ObjectManager) {
            $objectManager->persist($user);
            $objectManager->flush();
        }
    }
}
