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
use FSi\Bundle\AdminSecurityBundle\Behat\Page\Page;
use RuntimeException;

final class PasswordResetChangePassword extends Page
{
    /**
     * @param array<string, string|null> $urlParameters
     * @return void
     * @throws UnexpectedPageException
     */
    public function verify(array $urlParameters = []): void
    {
        parent::verify($urlParameters);

        if (false === $this->getDocument()->has('css', 'form[name="change_password"]')) {
            throw new UnexpectedPageException(
                sprintf(
                    'Page "%s" does not have a Password Reset Change form',
                    $this->getDriver()->getCurrentUrl()
                )
            );
        }
    }

    /**
     * @param array<string, string|null> $urlParameters
     * @return void
     */
    public function openWithoutVerification(array $urlParameters): void
    {
        $url = $this->getUrl($urlParameters);
        $this->getSession()->visit($url);
    }

    public function fillForm(): void
    {
        $this->fillField('New password', 'admin-new');
        $this->fillField('Repeat new password', 'admin-new');
    }

    public function fillFormWithInvalidData(): void
    {
        $this->fillField('New password', 'test1');
        $this->fillField('Repeat new password', 'test2');
    }

    /**
     * @param array<string, string|null> $urlParameters
     * @return string
     */
    protected function getUrl(array $urlParameters = []): string
    {
        $confirmationToken = $urlParameters['confirmationToken'] ?? null;
        if (null === $confirmationToken || '' === $confirmationToken) {
            throw new RuntimeException(sprintf(
                'No activation token for current page "%s"',
                $this->getDriver()->getCurrentUrl()
            ));
        }

        return $this->getParameter('base_url') . "/admin/password-reset/change-password/{$confirmationToken}";
    }
}
