<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page;

use FSi\Bundle\AdminSecurityBundle\Form\TypeSolver;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class PasswordResetChangePassword extends Page
{
    protected $path = '/admin/password-reset/change-password/{confirmationToken}';

    public function verifyPage(): void
    {
        $formName = TypeSolver::isSymfony3FormNamingConvention() ? 'change_password' : 'admin_password_reset_change_password';
        if (!$this->has('css', sprintf('form[name="%s"]', $formName))) {
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
