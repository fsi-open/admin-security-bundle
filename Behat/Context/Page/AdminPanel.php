<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page;

use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class AdminPanel extends Page
{
    protected $path = '/admin/';

    public function verifyPage(): void
    {
        if (!$this->has('css', 'h1#welcome')) {
            throw new UnexpectedPageException(sprintf("Page %s is not a Admin panel page", $this->path));
        }
        $this->verifyResponse();
    }

    public function getDropdownOptions(string $dropdownText): array
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

    public function getDropdown(string $dropdownText): ?NodeElement
    {
        $link = $this->find('css', sprintf('li#account a:contains("%s")', $dropdownText));
        if (!isset($link)) {
            return null;
        }

        return $this->find('css', 'li#account');
    }

    public function hasElementInTopMenu(string $element): bool
    {
        return $this->find('css', '#top-menu')->hasLink($element);
    }

    public function hasAnyMenuElements(): bool
    {
        return count($this->find('css', '#top-menu a')) !== 0;
    }
}

