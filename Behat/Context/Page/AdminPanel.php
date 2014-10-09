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

    public function verifyPage()
    {
        if (!$this->has('css', 'h1#welcome')) {
            throw new UnexpectedPageException(sprintf("Page %s is not a Admin panel page", $this->path));
        }
        $this->verifyResponse();
    }

    public function getDropdownOptions($dropdownText)
    {
        $link = $this->find('css', sprintf('li#account a:contains("%s")', $dropdownText));
        if (!isset($link)) {
            return null;
        }

        $linkNodes = $this->findAll('css', 'li#account > ul > li');

        return array_filter(array_map(function($element) {
            return $element->getText();
        }, $linkNodes));
    }

    public function getDropdown($dropdownText)
    {
        $link = $this->find('css', sprintf('li#account a:contains("%s")', $dropdownText));
        if (!isset($link)) {
            return null;
        }

        return $this->find('css', 'li#account');
    }

    public function hasElementInTopMenu($element)
    {
        return $this->find('css', '#top-menu')->hasLink($element);
    }
}

