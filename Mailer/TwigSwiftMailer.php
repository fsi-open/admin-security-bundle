<?php

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

    /**
     * @param Swift_Mailer $mailer
     * @param SwiftMessageFactoryInterface $messageFactory
     * @param string $templateName
     * @param string $fromEmail
     * @param string $replayToEmail
     */
    public function __construct(
        Swift_Mailer $mailer,
        SwiftMessageFactoryInterface $messageFactory,
        $templateName,
        $fromEmail,
        $replayToEmail = null
    ) {
        $this->mailer = $mailer;
        $this->messageFactory = $messageFactory;
        $this->templateName = $templateName;
        $this->fromEmail = $fromEmail;
        $this->replayToEmail = $replayToEmail;
    }

    /**
     * @param EmailableInterface $to
     * @return int
     */
    public function send(EmailableInterface $to)
    {
        $message = $this->messageFactory->createMessage($to->getEmail(), $this->templateName, array('receiver' => $to));
        $message->setFrom($this->fromEmail);
        if (isset($this->replayToEmail)) {
            $message->setReplyTo($this->replayToEmail);
        }

        return $this->mailer->send($message);
    }
}
