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

final class AdminChangePassword extends Page
{
    /**
     * @param array<string, string|null> $urlParameters
     * @return void
     */
    public function verify(array $urlParameters = []): void
    {
        parent::verify($urlParameters);
        if (false === $this->getDocument()->has('css', '#page-header:contains("Change password")')) {
            throw new UnexpectedPageException(
                "Page \"{$this->getDriver()->getCurrentUrl()}\" is not a Admin change password page."
            );
        }
    }

    public function findFieldError(string $field): ?NodeElement
    {
        $fieldElement = $this->getDocument()->findField($field);
        if (null === $fieldElement) {
            return null;
        }

        $fieldContainer = $fieldElement->getParent();
        return $fieldContainer->find('css', 'span.help-block');
    }

    /**
     * @param array<string, string|null> $urlParameters
     * @return string
     */
    protected function getUrl(array $urlParameters = []): string
    {
        return $this->getParameter('base_url') . '/admin/change-password';
    }
}
