<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Element;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Element\Element;
use RuntimeException;

final class Form extends Element
{
    public function getField(string $locator): ?NodeElement
    {
        $field = $this->getElement('form')->findField($locator);
        if (null === $field) {
            throw new RuntimeException("No form field \"{$locator}\"");
        }

        return $field;
    }

    public function getFieldErrors(string $fieldLocator): string
    {
        $field = $this->getElement('form')->findField($fieldLocator);
        if (null === $field) {
            throw new RuntimeException('');
        }

        $errorSelector = sprintf(
            'span.error[data-for="%s"]',
            $field->getAttribute('id')
        );

        $errors = $this->getDocument()->findAll('css', $errorSelector);
        return implode(
            ' ',
            array_map(
                static fn(NodeElement $item): string => $item->getText(),
                $errors
            )
        );
    }

    /**
     * @return array<string, string>
     */
    protected function getDefinedElements(): array
    {
        return ['form' => 'form'];
    }
}
