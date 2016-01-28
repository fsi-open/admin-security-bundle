<?php

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\Element;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class FlashMessage extends Element
{
    protected $selector = array('css' => '.alert');
}
