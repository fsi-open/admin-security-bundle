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
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserEnforcePasswordChangeInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserPasswordChangeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UserCreatedListener implements EventSubscriberInterface
{
    /**
     * @var TokenFactoryInterface
     */
    private $tokenFactory;

    /**
     * @param TokenFactoryInterface $tokenFactory
     */
    public function __construct(TokenFactoryInterface $tokenFactory)
    {

        $this->tokenFactory = $tokenFactory;
    }

    public static function getSubscribedEvents()
    {
        return array(
            AdminSecurityEvents::USER_CREATED => 'onUserCreated'
        );
    }

    /**
     * @param UserEvent $event
     */
    public function onUserCreated(UserEvent $event)
    {
        $user = $event->getUser();

        if (($user instanceof UserActivableInterface) && !$user->isEnabled()) {
            $user->setActivationToken($this->tokenFactory->createToken());
        }
    }
}
