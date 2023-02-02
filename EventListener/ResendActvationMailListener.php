<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ResendActvationMailListener implements EventSubscriberInterface
{
    private MailerInterface $mailer;
    private TokenFactoryInterface $tokenFactory;

    public function __construct(MailerInterface $mailer, TokenFactoryInterface $tokenFactory)
    {
        $this->mailer = $mailer;
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [AdminSecurityEvents::RESEND_ACTIVATION_TOKEN => 'resendActivationMail'];
    }

    public function resendActivationMail(ActivationEvent $event): void
    {
        $user = $event->getUser();
        if (true === $user->isEnabled()) {
            return;
        }

        $user->removeActivationToken();
        $user->setActivationToken($this->tokenFactory->createToken());
        $this->mailer->send($user);
    }
}
