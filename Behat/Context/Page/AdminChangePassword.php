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

class AdminChangePassword extends Page
{
    protected $path = '/admin/change-password';

    public function verifyPage(): void
    {
        if (!$this->has('css', '#page-header:contains("Change password")')) {
            throw new UnexpectedPageException(sprintf("Page %s is not a Admin change password page", $this->path));
        }
        $this->verifyResponse();
    }

    public function findFieldError(string $field): ?NodeElement
    {
        $fieldContainer = $this->findField($field)->getParent();
        return $fieldContainer->find('css', 'span.help-block');
    }
}
