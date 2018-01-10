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
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendPasswordResetMailListener implements EventSubscriberInterface
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var TokenFactoryInterface
     */
    private $tokenFactory;

    public function __construct(MailerInterface $mailer, TokenFactoryInterface $tokenFactory)
    {
        $this->mailer = $mailer;
        $this->tokenFactory = $tokenFactory;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AdminSecurityEvents::RESET_PASSWORD_REQUEST => 'onResetPasswordRequest'
        ];
    }

    public function onResetPasswordRequest(ResetPasswordRequestEvent $event): void
    {
        $user = $event->getUser();
        $user->setPasswordResetToken($this->tokenFactory->createToken());
        $this->mailer->send($user);
    }
}
