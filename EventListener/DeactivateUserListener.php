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
use FSi\Bundle\AdminSecurityBundle\Event\DeactivationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeactivateUserListener implements EventSubscriberInterface
{
    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AdminSecurityEvents::DEACTIVATION => 'onDeactivation'
        ];
    }

    public function onDeactivation(DeactivationEvent $event): void
    {
        $event->getUser()->setEnabled(false);
    }
}
