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
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DoctrineUserListener implements EventSubscriberInterface
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
            AdminSecurityEvents::RESET_PASSWORD_REQUEST => 'onResetPasswordRequest'
        );
    }

    /**
     * @param ChangePasswordEvent $event
     */
    public function onChangePassword(ChangePasswordEvent $event)
    {
        $user = $event->getUser();
        $this->flushUserObjectManager($user);
    }

    /**
     * @param ResetPasswordRequestEvent $event
     */
    public function onResetPasswordRequest(ResetPasswordRequestEvent $event)
    {
        $user = $event->getUser();
        $this->flushUserObjectManager($user);
    }

    /**
     * @param object $user
     */
    private function flushUserObjectManager($user)
    {
        $objectManager = $this->registry->getManagerForClass(get_class($user));

        if ($objectManager instanceof ObjectManager) {
            $objectManager->flush();
        }
    }
}
