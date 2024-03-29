<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Controller;

use FSi\Bundle\AdminBundle\Message\FlashMessages;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

class LoginController
{
    private Environment $twig;
    private AuthenticationUtils $authenticationUtils;
    private FlashMessages $flashMessages;
    private string $loginActionTemplate;

    public function __construct(
        Environment $twig,
        AuthenticationUtils $authenticationUtils,
        FlashMessages $flashMessages,
        string $loginActionTemplate
    ) {
        $this->twig = $twig;
        $this->authenticationUtils = $authenticationUtils;
        $this->flashMessages = $flashMessages;
        $this->loginActionTemplate = $loginActionTemplate;
    }

    public function __invoke(): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        if (null !== $error) {
            $this->flashMessages->error($error->getMessageKey(), $error->getMessageData(), 'security');
        }

        return new Response($this->twig->render(
            $this->loginActionTemplate,
            ['last_username' => $this->authenticationUtils->getLastUsername()]
        ));
    }
}
