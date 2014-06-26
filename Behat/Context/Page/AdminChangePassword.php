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

class AdminChangePassword extends Page
{
    protected $path = '/admin/change-password';

    public function isOpen(array $urlParameters = array())
    {
        if (!$this->has('css', 'form')) {
            throw new UnexpectedPageException(sprintf("Page %s is not a Admin change password page", $this->path));
        }
        $this->verify($urlParameters);
    }

    public function findFieldError($field)
    {
        $fieldContainer = $this->findField($field)->getParent();
        return $fieldContainer->find('css', 'span.help-block');
    }
}

