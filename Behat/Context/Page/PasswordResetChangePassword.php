<?php

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page;

use FSi\Bundle\AdminSecurityBundle\Form\TypeSolver;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class PasswordResetChangePassword extends Page
{
    protected $path = '/admin/password-reset/change-password/{confirmationToken}';

    public function verifyPage()
    {
        $formName = TypeSolver::isSymfony3FormNamingConvention() ? 'change_password' : 'admin_password_reset_change_password';
        if (!$this->has('css', sprintf('form[name="%s"]', $formName))) {
            throw new UnexpectedPageException(
                sprintf("Page %s does not have a Password Reset Change form", $this->path)
            );
        }
        $this->verifyResponse();
    }

    public function openWithoutVerification($urlParameters)
    {
        $url = $this->getUrl($urlParameters);
        $this->getSession()->visit($url);
    }

    public function fillForm()
    {
        $this->fillField('New password', 'admin-new');
        $this->fillField('Repeat new password', 'admin-new');
    }

    public function fillFormWithInvalidData()
    {
        $this->fillField('New password', 'test1');
        $this->fillField('Repeat new password', 'test2');
    }
}
