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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @property UrlGeneratorInterface $urlGenerator
 * @property FlashMessages $flashMessages
 */
trait FlashWithRedirect
{
    /**
     * @param array<string, string> $routeParameters
     */
    private function addFlashAndRedirect(
        string $flashType,
        string $flashMessage,
        string $route = 'fsi_admin_security_user_login',
        array $routeParameters = []
    ): Response {
        $this->flashMessages->{$flashType}($flashMessage, [], 'FSiAdminSecurity');

        return new RedirectResponse($this->urlGenerator->generate($route, $routeParameters));
    }
}
