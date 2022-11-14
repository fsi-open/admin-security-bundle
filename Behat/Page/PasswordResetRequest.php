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

class PasswordResetRequest extends Page
{
    /**
     * @param array<string, string|null> $urlParameters
     * @return void
     * @throws UnexpectedPageException
     */
    public function verify(array $urlParameters = []): void
    {
        parent::verify($urlParameters);

        if (false === $this->getDocument()->has('css', 'form[name="request"]')) {
            throw new UnexpectedPageException(
                sprintf(
                    'Page "%s" does not have a Password Reset Request form',
                    $this->getDriver()->getCurrentUrl()
                )
            );
        }
    }

    public function getMessage(): string
    {
        return $this->find('css', '.alert')->getText();
    }

    /**
     * @param array<string, string|null> $urlParameters
     * @return string
     */
    protected function getUrl(array $urlParameters = []): string
    {
        return $this->getParameter('base_url') . '/admin/password-reset/request';
    }
}
