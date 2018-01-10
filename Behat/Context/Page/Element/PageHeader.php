<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\Element;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class PageHeader extends Element
{
    protected $selector = ['css' => '#page-header'];
}
