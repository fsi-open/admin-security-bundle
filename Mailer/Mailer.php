<?php

namespace FSi\Bundle\AdminSecurityBundle\Mailer;

use FSi\Bundle\AdminSecurityBundle\Model\UserPasswordResetInterface;
use Swift_Mailer;
use Symfony\Component\Routing\RouterInterface;
use Twig_Environment;

class Mailer implements MailerInterface
{
    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string
     */
    private $fromEmail;

    /**
     * @var string
     */
    private $templateName;

    public function __construct(
        Swift_Mailer $mailer,
        Twig_Environment $twig,
        RouterInterface $router,
        $fromEmail,
        $templateName
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->router = $router;
        $this->fromEmail = $fromEmail;
        $this->templateName = $templateName;
    }

    public function sendPasswordResetMail(UserPasswordResetInterface $user)
    {
        list($subject, $htmlBody) = $this->prepareMessage(array('user' => $user));

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setTo($user->getEmail())
            //->setReplyTo($this->toEmail)
            ->setFrom($this->fromEmail)
        ;

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html');
        }

        $temp = $this->mailer->send($message);

        return $temp;
    }

    protected function prepareMessage($data)
    {
        $templateContext = $this->twig->mergeGlobals($data);
        /** @var \Twig_Template $template */
        $template = $this->twig->loadTemplate($this->templateName);
        $subject = $template->renderBlock('subject', $templateContext);
        $htmlBody = $template->renderBlock('body_html', $templateContext);

        return array($subject, $htmlBody);
    }
}
