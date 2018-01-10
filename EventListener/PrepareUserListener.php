<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\FormEvent;
use FSi\Bundle\AdminBundle\Event\FormEvents;
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;

class PrepareUserListener implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::FORM_DATA_PRE_SAVE => [
                'prepareAndDispatchUserCreated',
            ]
        ];
    }

    public function prepareAndDispatchUserCreated(FormEvent $event): void
    {
        $entity = $event->getForm()->getData();

        if (!$entity instanceof UserInterface) {
            return;
        }

        // do not dispatch event and do not set random password for existing users
        if ($entity->getPassword()) {
            return;
        }

        $entity->setEnabled(false);
        $entity->enforcePasswordChange(true);
        $entity->setPlainPassword(random_bytes(32));

        $this->eventDispatcher->dispatch(
            AdminSecurityEvents::USER_CREATED,
            new UserEvent($entity)
        );
    }
}
