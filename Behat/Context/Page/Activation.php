<?php

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Activation extends Page
{
    protected $path = '/admin/activation/activate/{activationToken}';

    public function verifyPage()
    {
        $this->verifyResponse();
    }

    public function openWithoutVerification($urlParameters)
    {
        $url = $this->getUrl($urlParameters);
        $this->getSession()->visit($url);
    }
}
