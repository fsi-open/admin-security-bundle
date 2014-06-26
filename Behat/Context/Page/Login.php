<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Login extends Page
{
    protected $path = '/admin/login';

    public function isOpen(array $urlParameters = array())
    {
        if (!$this->has('css', 'form.form-signin')) {
            throw new UnexpectedPageException(sprintf("Page %s is not a Login page", $this->path));
        }
        $this->verify($urlParameters);
    }

    public function getFormErrorMessage()
    {
        return $this->find('css', 'form > div.alert-danger')->getText();
    }

    public function getFormSuccessMessage()
    {
        return $this->find('css', 'form > div.alert-success')->getText();
    }
}

