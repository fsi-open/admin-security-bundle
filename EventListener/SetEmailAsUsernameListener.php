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
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SetEmailAsUsernameListener implements EventSubscriberInterface
{
    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AdminSecurityEvents::USER_CREATED => 'setEmailAsUsername'
        ];
    }

    public function setEmailAsUsername(UserEvent $event): void
    {
        $user = $event->getUser();
        if (false === $user instanceof UserInterface) {
            return;
        }

        $email = $user->getEmail();
        if (null === $email) {
            throw new RuntimeException(sprintf(
                'No email for user of class "%s"',
                get_class($user)
            ));
        }

        $user->setUsername($email);
    }
}
