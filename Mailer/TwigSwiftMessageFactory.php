<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Mailer;

use Swift_Message;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Template;

class TwigSwiftMessageFactory implements SwiftMessageFactoryInterface
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(Environment $twig, RequestStack $requestStack)
    {
        $this->twig = $twig;
        $this->requestStack = $requestStack;
    }

    public function createMessage(string $email, string $template, array $data): Swift_Message
    {
        $masterRequest = $this->requestStack->getMasterRequest();

        if (null !== $masterRequest) {
            $data['request'] = $masterRequest;
        }

        $templateContext = $this->twig->mergeGlobals($data);

        /** @var Template $template */
        $template = $this->twig->loadTemplate($template);
        $subject = $template->renderBlock('subject', $templateContext);
        $htmlBody = $template->renderBlock('body_html', $templateContext);
        $message = new Swift_Message($subject);
        $message->setTo($email);

        if ('' !== $htmlBody) {
            $message->setBody($htmlBody, 'text/html');
        }

        return $message;
    }
}
