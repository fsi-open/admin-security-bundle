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

class Login extends Page
{
    protected $path = '/admin/login';

    public function verifyPage(): void
    {
        if (!$this->has('css', 'form.form-signin')) {
            throw new UnexpectedPageException(sprintf("Page %s is not a Login page", $this->path));
        }
        $this->verifyResponse();
    }

    public function getFormErrorMessage(): string
    {
        return $this->find('css', 'form > div.alert-danger')->getText();
    }
}
