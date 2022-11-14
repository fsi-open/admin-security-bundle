<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Page;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Page\UnexpectedPageException;

use function array_filter;
use function array_map;

final class AdminPanel extends Page
{
    /**
     * @param array<string, string|null> $urlParameters
     * @return void
     */
    public function verify(array $urlParameters = []): void
    {
        parent::verify($urlParameters);
        if (false === $this->getDocument()->has('css', 'h1#welcome')) {
            throw new UnexpectedPageException(
                "Page \"{$this->getDriver()->getCurrentUrl()}\" is not a Admin panel page"
            );
        }
    }

    /**
     * @param string $dropdownText
     * @return array<string>
     */
    public function getDropdownOptions(string $dropdownText): array
    {
        $link = $this->getDocument()->find('css', "li#account a:contains(\"{$dropdownText}\")");
        if (null === $link) {
            return [];
        }

        $linkNodes = $this->getDocument()->findAll('css', 'li#account > ul > li');
        return array_filter(
            array_map(
                static fn(NodeElement $element): string => $element->getText(),
                $linkNodes
            )
        );
    }

    public function getDropdown(string $dropdownText): ?NodeElement
    {
        $link = $this->getDocument()->find('css', "li#account a:contains(\"{$dropdownText}\")");
        if (null === $link) {
            return null;
        }

        return $this->getDocument()->find('css', 'li#account');
    }

    public function hasElementInTopMenu(string $element): bool
    {
        return $this->getDocument()->find('css', '#top-menu')->hasLink($element);
    }

    public function hasAnyMenuElements(): bool
    {
        $elements = $this->getDocument()->find('css', '#top-menu a');
        return null !== $elements && true === $elements->isValid();
    }

    /**
     * @param array<string, string|null> $urlParameters
     * @return string
     */
    protected function getUrl(array $urlParameters = []): string
    {
        return $this->getParameter('base_url') . '/admin/';
    }
}
