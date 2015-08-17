<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $registry;

    /**
     * @param Registry $registry
     */
    function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AdminSecurityEvents::CHANGE_PASSWORD => 'onChangePassword',
            AdminSecurityEvents::RESET_PASSWORD_REQUEST => 'onResetPasswordRequest',
            AdminSecurityEvents::ACTIVATION => 'onActivation',
            AdminSecurityEvents::DEACTIVATION => 'onDeactivation',
            AdminSecurityEvents::USER_CREATED => 'onUserCreated',
            AdminSecurityEvents::PROMOTE_USER => 'onPromoteUser',
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin'
        );
    }

    /**
     * @param ActivationEvent $event
     */
    public function onActivation(ActivationEvent $event)
    {
        $this->flushUserObjectManager($event->getUser());
    }

    /**
     * @param ActivationEvent $event
     */
    public function onDeactivation(ActivationEvent $event)
    {
        $this->flushUserObjectManager($event->getUser());
    }

    /**
     * @param ChangePasswordEvent $event
     */
    public function onChangePassword(ChangePasswordEvent $event)
    {
        $this->flushUserObjectManager($event->getUser());
    }

    /**
     * @param ResetPasswordRequestEvent $event
     */
    public function onResetPasswordRequest(ResetPasswordRequestEvent $event)
    {
        $this->flushUserObjectManager($event->getUser());
    }

    /**
     * @param UserEvent $event
     */
    public function onUserCreated(UserEvent $event)
    {
        $this->flushUserObjectManager($event->getUser());
    }

    /**
     * @param UserEvent $event
     */
    public function onPromoteUser(UserEvent $event)
    {
        $this->flushUserObjectManager($event->getUser());
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $this->flushUserObjectManager($event->getAuthenticationToken()->getUser());
    }

    /**
     * @param object $user
     */
    private function flushUserObjectManager($user)
    {
        $objectManager = $this->registry->getManagerForClass(get_class($user));

        if ($objectManager instanceof ObjectManager) {
            $objectManager->persist($user);
            $objectManager->flush();
        }
    }
}
