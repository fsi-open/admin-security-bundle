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

class ActivationChangePassword extends Page
{
    protected $path = '/admin/activation/change-password/{activationToken}';

    public function verifyPage(): void
    {
        if (!$this->has('css', 'form[name="admin_password_reset_change_password"]')) {
            throw new UnexpectedPageException(sprintf("Page %s is not an Activation Request", $this->path));
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
