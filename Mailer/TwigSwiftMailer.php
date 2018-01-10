<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Mailer;

use Swift_Mailer;

class TwigSwiftMailer implements MailerInterface
{
    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var SwiftMessageFactoryInterface
     */
    private $messageFactory;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @var string
     */
    private $fromEmail;

    /**
     * @var string|null
     */
    private $replayToEmail;

    public function __construct(
        Swift_Mailer $mailer,
        SwiftMessageFactoryInterface $messageFactory,
        string $templateName,
        string $fromEmail,
        ?string $replayToEmail = null
    ) {
        $this->mailer = $mailer;
        $this->messageFactory = $messageFactory;
        $this->templateName = $templateName;
        $this->fromEmail = $fromEmail;
        $this->replayToEmail = $replayToEmail;
    }

    public function send(EmailableInterface $to): int
    {
        $message = $this->messageFactory->createMessage($to->getEmail(), $this->templateName, ['receiver' => $to]);
        $message->setFrom($this->fromEmail);
        if (null !== $this->replayToEmail) {
            $message->setReplyTo($this->replayToEmail);
        }

        return $this->mailer->send($message);
    }
}
