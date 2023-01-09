<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\FormDataPreSaveEvent;
use FSi\Bundle\AdminBundle\Event\FormEvent;
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;

use function bin2hex;
use function random_bytes;

class PrepareUserListener implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormDataPreSaveEvent::class => 'prepareAndDispatchUserCreated'
        ];
    }

    public function prepareAndDispatchUserCreated(FormEvent $event): void
    {
        $entity = $event->getForm()->getData();
        if (false === $entity instanceof UserInterface) {
            return;
        }

        // do not dispatch event and do not set random password for existing users
        if (null !== $entity->getPassword()) {
            return;
        }

        $entity->setEnabled(false);
        $entity->enforcePasswordChange(true);
        $entity->setPlainPassword(bin2hex(random_bytes(32)));

        $this->eventDispatcher->dispatch(new UserEvent($entity), AdminSecurityEvents::USER_CREATED);
    }
}
