<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Page;

use FriendsOfBehat\PageObjectExtension\Page\UnexpectedPageException;

final class UserEdit extends Page
{
    /**
     * @param array<string, string|null> $urlParameters
     * @return void
     * @throws UnexpectedPageException
     */
    public function verify(array $urlParameters = []): void
    {
        parent::verify($urlParameters);

        if (false === $this->getDocument()->has('css', '#page-header:contains("Edit element")')) {
            throw new UnexpectedPageException(
                sprintf("%s page is missing \"Edit element\" header", $this->getUrl($urlParameters))
            );
        }
    }

    /**
     * @param array<string, string|null> $urlParameters
     * @return string
     */
    protected function getUrl(array $urlParameters = []): string
    {
        return $this->getParameter('base_url') . '/admin/en/form/admin_security_user/' . $urlParameters['id'];
    }
}
