<?php

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\Element;

use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;

class Form extends Element
{
    protected $selector = array('css' => 'form');

    public function getField($locator)
    {
        $field = $this->findField($locator);

        if (!isset($field)) {
            throw new ElementNotFoundException(sprintf('Form "%s" field', $locator));
        }

        return $field;
    }

    public function getFieldErrors($fieldLocator)
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
