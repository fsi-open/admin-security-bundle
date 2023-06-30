<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Mailer;

use RuntimeException;
use Swift_Mailer;

class TwigSwiftMailer implements MailerInterface
{
    private Swift_Mailer $mailer;
    private SwiftMessageFactoryInterface $messageFactory;
    private string $templateName;
    private string $fromEmail;
    private ?string $replyToEmail;

    public function __construct(
        Swift_Mailer $mailer,
        SwiftMessageFactoryInterface $messageFactory,
        string $templateName,
        string $fromEmail,
        ?string $replyToEmail = null
    ) {
        $this->mailer = $mailer;
        $this->messageFactory = $messageFactory;
        $this->templateName = $templateName;
        $this->fromEmail = $fromEmail;
        $this->replyToEmail = $replyToEmail;
    }

    public function send(EmailableInterface $to): int
    {
        $recipient = $to->getEmail();
        if (null === $recipient) {
            throw new RuntimeException(sprintf(
                'No recipient for object of class "%s"',
                get_class($to)
            ));
        }

        $message = $this->messageFactory->createMessage($recipient, $this->templateName, ['receiver' => $to]);
        $message->setFrom($this->fromEmail);
        if (null !== $this->replyToEmail) {
            $message->setReplyTo($this->replyToEmail);
        }

        return $this->mailer->send($message);
    }
}
