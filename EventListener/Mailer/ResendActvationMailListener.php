<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener\Mailer;

use FSi\Bundle\AdminSecurityBundle\Event\ActivationTokenResetEvent;
use FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ResendActvationMailListener implements EventSubscriberInterface
{
    private MailerInterface $mailer;

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [ActivationTokenResetEvent::class => 'resendActivationMail'];
    }

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function resendActivationMail(ActivationTokenResetEvent $event): void
    {
        $this->mailer->send($event->getUser());
    }
}
