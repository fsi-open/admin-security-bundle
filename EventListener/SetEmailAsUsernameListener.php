<?php
/**
 * Created by PhpStorm.
 * User: piotr
 * Date: 8/27/15
 * Time: 9:49 AM
 */

namespace FSi\Bundle\AdminSecurityBundle\EventListener;


use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SetEmailAsUsernameListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AdminSecurityEvents::USER_CREATED => 'setEmailAsUsername'
        );
    }

    /**
     * @param UserEvent $event
     *
     * @throws \Exception
     */
    public function setEmailAsUsername(UserEvent $event)
    {
        $user = $event->getUser();
        if (!$user instanceof UserInterface) {
            throw new \Exception('User entity should implement UserInterface');
        }

        $user->setUsername($user->getEmail());
    }
}
