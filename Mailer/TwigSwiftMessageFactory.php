<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Mailer;

use FSi\Bundle\AdminSecurityBundle\Security\User\UserActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserPasswordResetInterface;
use Swift_Mailer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig_Environment;

class TwigSwiftMessageFactory implements SwiftMessageFactoryInterface
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        Twig_Environment $twig,
        RequestStack $requestStack
    ) {
        $this->twig = $twig;
        $this->requestStack = $requestStack;
    }

    /**
     * @param string $email
     * @param string $template
     * @param array $data
     * @return \Swift_Message
     */
    public function createMessage($email, $template, array $data)
    {
        $masterRequest = $this->requestStack->getMasterRequest();

        if (!empty($masterRequest)) {
            $data['request'] = $masterRequest;
        }

        $templateContext = $this->twig->mergeGlobals($data);

        /** @var \Twig_Template $template */
        $template = $this->twig->loadTemplate($template);
        $subject = $template->renderBlock('subject', $templateContext);
        $htmlBody = $template->renderBlock('body_html', $templateContext);
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setTo($email);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html');
        }

        return $message;
    }
}
