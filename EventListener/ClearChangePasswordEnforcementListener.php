<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\Model\EnforceablePasswordChangeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearChangePasswordEnforcementListener implements EventSubscriberInterface
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

        if (($user instanceof EnforceablePasswordChangeInterface) && $user->isForcedToChangePassword()) {
            $user->enforcePasswordChange(false);
        }
    }
}
