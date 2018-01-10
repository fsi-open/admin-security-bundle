<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\Element;

use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;

class Form extends Element
{
    protected $selector = ['css' => 'form'];

    public function getField(string $locator): ?NodeElement
    {
        $field = $this->findField($locator);

        if (!isset($field)) {
            throw new ElementNotFoundException(sprintf('Form "%s" field', $locator));
        }

        return $field;
    }

    public function getFieldErrors(string $fieldLocator): string
    {
        $errorSelector = sprintf(
            'span.error[data-for="%s"]',
            $this->findField($fieldLocator)->getAttribute('id')
        );

        $errors = $this->findAll('css', $errorSelector);

        return implode(' ', array_map(function (NodeElement $item) {
            return $item->getText();
        }, $errors));
    }
}
