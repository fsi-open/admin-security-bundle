<?php

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class PasswordResetRequest extends Page
{
    protected $path = '/admin/password-reset/request';

    public function verifyPage()
    {
        if (!$this->has('css', 'form[name="admin_password_reset_request"]')) {
            throw new UnexpectedPageException(sprintf("Page %s is not a Password Reset Request", $this->path));
        }
        $this->verifyResponse();
    }

    public function getMessage()
    {
        return $this->find('css', '.flash')->getText();
    }
}
