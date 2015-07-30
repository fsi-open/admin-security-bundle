<?php

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserEnforcePasswordChangeInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserPasswordChangeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UserPasswordChangedListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            AdminSecurityEvents::CHANGE_PASSWORD => 'onChangePassword'
        );
    }

    /**
     * @param ChangePasswordEvent $event
     */
    public function onChangePassword(ChangePasswordEvent $event)
    {
        $user = $event->getUser();

        if (($user instanceof UserEnforcePasswordChangeInterface) && $user->hasEnforcedPasswordChange()) {
            $user->enforcePasswordChange(false);
        }
    }
}
