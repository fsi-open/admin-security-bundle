<?php

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page;

use FSi\Bundle\AdminSecurityBundle\Form\TypeSolver;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class PasswordResetRequest extends Page
{
    protected $path = '/admin/password-reset/request';

    public function verifyPage()
    {
        $formName = TypeSolver::isSymfony3FormNamingConvention() ? 'request' : 'admin_password_reset_request';
        if (!$this->has('css', sprintf('form[name="%s"]', $formName))) {
            throw new UnexpectedPageException(
                sprintf("Page %s does not have a Password Reset Request form", $this->path)
            );
        }

        $this->verifyResponse();
    }

    public function getMessage()
    {
        return $this->find('css', '.alert')->getText();
    }
}
