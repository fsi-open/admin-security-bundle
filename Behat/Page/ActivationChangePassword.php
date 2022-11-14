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
use function sprintf;

final class ActivationChangePassword extends Page
{
    /**
     * @param array<string, string|null> $urlParameters
     * @return void
     */
    public function verify(array $urlParameters = []): void
    {
        parent::verify($urlParameters);
        if (false === $this->getDocument()->has('css', 'form[name="admin_password_reset_change_password"]')) {
            throw new UnexpectedPageException(
                sprintf(
                    'Page "%s" is not an Activation Request',
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
        $activationToken = $urlParameters['activationToken'] ?? null;
        if (null === $activationToken || '' === $activationToken) {
            throw new RuntimeException(sprintf(
                'No activation token for current page "%s"',
                $this->getDriver()->getCurrentUrl()
            ));
        }

        return $this->getParameter('base_url') . "/admin/activation/change-password/{$activationToken}";
    }
}
