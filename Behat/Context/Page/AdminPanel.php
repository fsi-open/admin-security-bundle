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

class AdminPanel extends Page
{
    protected $path = '/admin';

    public function isOpen()
    {
        if (!$this->has('css', 'h1#welcome')) {
            throw new UnexpectedPageException(sprintf("Page %s is not a Admin panel page", $this->path));
        }
        $this->verifyResponse();
    }
}

