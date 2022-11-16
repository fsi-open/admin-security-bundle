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
        return [AdminSecurityEvents::USER_CREATED => 'onUserCreated'];
    }

    public function onUserCreated(UserEvent $event): void
    {
        $user = $event->getUser();
        if (false === $user instanceof ActivableInterface || true === $user->isEnabled()) {
            return;
        }

        $user->setActivationToken($this->tokenFactory->createToken());
        $this->mailer->send($user);
    }
}
