<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResetPasswordRequestMailerListener implements EventSubscriberInterface
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            AdminSecurityEvents::RESET_PASSWORD_REQUEST => 'onResetPasswordRequest'
        );
    }

    /**
     * @param ResetPasswordRequestEvent $event
     */
    public function onResetPasswordRequest(ResetPasswordRequestEvent $event)
    {
        $this->mailer->sendPasswordResetMail($event->getUser());
    }
}
