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
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class UserList extends Page
{
    protected $path = '/admin/list/admin_security_user';
    protected $elements = [
        'table' => '#admin_security_user',
        'batch actions' => '#batch_action_action',
    ];

    public function verifyPage(): void
    {
        if (!$this->hasElement('table')) {
            throw new UnexpectedPageException('Unable to find users table');
        }
        $this->verifyResponse();
    }

    public function getUsersCount(): int
    {
        $records = $this->getElement('table')->findAll('css', 'tbody > tr');

        return count($records);
    }

    public function getBatchActionsElement(): Element
    {
        return $this->getElement('batch actions');
    }

    public function getBatchActions(): array
    {
        $options = $this->getElement('batch actions')->findAll('css', 'option');

        $options = array_map(function(NodeElement $item) {
            return $item->getText();
        }, $options);

        return array_filter($options, function($item) {
            if ($item === 'Select action') {
                return false;
            }
            return true;
        });
    }
}
