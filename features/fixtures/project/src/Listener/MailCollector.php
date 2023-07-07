<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\FixturesBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Email;

final class MailCollector implements EventSubscriberInterface
{
    /**
     * @var array<Email>
     */
    private static array $emails;

    /**
     * @return array<class-string<object>, array<string|int>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => ['onMessage', -255],
        ];
    }

    public function __construct()
    {
        $this->resetEmails();
    }

    public function onMessage(MessageEvent $event): void
    {
        $email = $event->getMessage();
        if (false === $email instanceof Email) {
            return;
        }

        self::$emails[] = $email;
    }

    /**
     * @return array<Email>
     */
    public function getEmails(): array
    {
        return self::$emails;
    }

    public function resetEmails(): void
    {
        self::$emails = [];
    }
}
