<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class PasswordResetChangePassword extends Page
{
    protected $path = '/admin/password-reset/change-password/{confirmationToken}';

    public function verifyPage(): void
    {
        if (false === $this->has('css', 'form[name="change_password"]')) {
            throw new UnexpectedPageException(
                sprintf("Page %s does not have a Password Reset Change form", $this->path)
            );
        }
        $this->verifyResponse();
    }

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
}
