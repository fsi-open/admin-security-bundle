<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Element;

use FriendsOfBehat\PageObjectExtension\Element\Element;

final class PageHeader extends Element
{
    public function getText(): string
    {
        return $this->getElement('page header')->getText();
    }

    /**
     * @return array<string, string>
     */
    protected function getDefinedElements(): array
    {
        return ['page header' => '#page-header'];
    }
}
