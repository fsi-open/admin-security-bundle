<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\FormEvent;
use FSi\Bundle\AdminBundle\Event\FormEvents;
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use Symfony\Component\Security\Core\Util\SecureRandomInterface;

class PrepareUserListener implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var SecureRandomInterface
     */
    private $secureRandom;

    public function __construct(EventDispatcherInterface $eventDispatcher, SecureRandomInterface $secureRandom)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->secureRandom = $secureRandom;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::FORM_DATA_PRE_SAVE => array(
                'prepareAndDispatchUserCreated',
            )
        );
    }

    public function prepareAndDispatchUserCreated(FormEvent $event)
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
        $entity->setPlainPassword($this->secureRandom->nextBytes(32));

        $this->eventDispatcher->dispatch(
            AdminSecurityEvents::USER_CREATED,
            new UserEvent($entity)
        );
    }
}
