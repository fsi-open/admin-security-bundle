<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Mailer;

use FSi\Bundle\AdminSecurityBundle\Mailer\Exception\MailerException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function get_class;
use function sprintf;

final class Mailer implements MailerInterface
{
    private SymfonyMailer $mailer;
    private TranslatorInterface $translator;
    private ?BodyRendererInterface $bodyRenderer;
    private string $subject;
    private string $templateName;
    private string $fromEmail;
    private ?string $replyToEmail;

    public function __construct(
        SymfonyMailer $mailer,
        TranslatorInterface $translator,
        ?BodyRendererInterface $bodyRenderer,
        string $subject,
        string $templateName,
        string $fromEmail,
        ?string $replyToEmail = null
    ) {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->bodyRenderer = $bodyRenderer;
        $this->subject = $subject;
        $this->templateName = $templateName;
        $this->fromEmail = $fromEmail;
        $this->replyToEmail = $replyToEmail;
    }

    public function send(EmailableInterface $to): bool
    {
        $recipient = $to->getEmail();
        if (null === $recipient || '' === $recipient) {
            throw new MailerException(sprintf(
                'No recipient for object of class "%s"',
                get_class($to)
            ));
        }

        $message = (new TemplatedEmail())
            ->from($this->fromEmail)
            ->to(new Address($recipient))
            ->subject(
                $this->translator->trans($this->subject, [], 'FSiAdminSecurity')
            )
            ->htmlTemplate($this->templateName)
            ->context(['receiver' => $to])
        ;

        if (null !== $this->replyToEmail) {
            $message->replyTo(new Address($this->replyToEmail));
        }

        if (null !== $this->bodyRenderer) {
            $this->bodyRenderer->render($message);
        }

        $this->mailer->send($message);

        return true;
    }
}
