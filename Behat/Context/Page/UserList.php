<?php

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page;

use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class UserList extends Page
{
    protected $path = '/admin/list/admin_security_user';
    protected $elements = [
        'table' => '#admin_security_user',
        'batch actions' => '#batch_action_action',
    ];

    public function verifyPage()
    {
        if (!$this->hasElement('table')) {
            throw new UnexpectedPageException('Unable to find users table');
        }
        $this->verifyResponse();
    }

    public function getUsersCount()
    {
        $records = $this->getElement('table')->findAll('css', 'tbody > tr');

        return count($records);
    }

    public function getBatchActionsElement()
    {
        return $this->getElement('batch actions');
    }

    /**
     * @return bool
     */
    public function getBatchActions()
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
