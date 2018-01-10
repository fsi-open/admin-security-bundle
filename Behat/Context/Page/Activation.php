<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Activation extends Page
{
    protected $path = '/admin/activation/activate/{activationToken}';

    public function verifyPage(): void
    {
        $this->verifyResponse();
    }

    public function openWithoutVerification(array $urlParameters): void
    {
        $url = $this->getUrl($urlParameters);
        $this->getSession()->visit($url);
    }
}
