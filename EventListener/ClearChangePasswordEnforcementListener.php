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
use FSi\Bundle\AdminSecurityBundle\Security\User\EnforceablePasswordChangeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearChangePasswordEnforcementListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AdminSecurityEvents::CHANGE_PASSWORD => 'onChangePassword'
        ];
    }

    public function onChangePassword(ChangePasswordEvent $event): void
    {
        $user = $event->getUser();

        if (($user instanceof EnforceablePasswordChangeInterface) && $user->isForcedToChangePassword()) {
            $user->enforcePasswordChange(false);
        }
    }
}
