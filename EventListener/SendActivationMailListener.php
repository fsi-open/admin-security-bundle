<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendActivationMailListener implements EventSubscriberInterface
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var TokenFactoryInterface
     */
    private $tokenFactory;

    function __construct(MailerInterface $mailer, TokenFactoryInterface $tokenFactory)
    {
        $this->mailer = $mailer;
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * {@inheritdoc}
     */
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

        if (($user instanceof ActivableInterface) && !$user->isEnabled()) {
            $user->setActivationToken($this->tokenFactory->createToken());
            $this->mailer->send($event->getUser());
        }
    }
}
