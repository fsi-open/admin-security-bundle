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
use RuntimeException;

final class Login extends Page
{
    /**
     * @param array<string, string|null> $urlParameters
     * @return void
     */
    public function verify(array $urlParameters = []): void
    {
        parent::verify($urlParameters);

        if (false === $this->getDocument()->has('css', 'form.form-signin')) {
            throw new UnexpectedPageException(
                "Page \"{$this->getDriver()->getCurrentUrl()}\" is not a Login page"
            );
        }
    }

    public function getFormErrorMessage(): string
    {
        $messageElement = $this->find('css', 'form > div.alert-danger');
        if (null === $messageElement) {
            throw new RuntimeException('No element for "form > div.alert-danger".');
        }

        return $messageElement->getText();
    }

    /**
     * @param array<string, string|null> $urlParameters
     * @return string
     */
    protected function getUrl(array $urlParameters = []): string
    {
        return $this->getParameter('base_url') . '/admin/login';
    }
}
