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

    /**
     * @param int $number
     * @return NodeElement
     */
    public function getRow($number)
    {
        $row = $this->find('xpath', '//tbody/tr[' . $number . ']');

        if (!isset($row)) {
            throw new UnexpectedPageException(sprintf('Row "%s" does not exist in DataGrid', $number));
        }

        return $row;
    }
    /**
     * @param int $rowNum
     * @param int $columnNum
     */
    public function checkCellCheckbox($rowNum, $columnNum = 1)
    {
        $row = $this->getRow($rowNum);
        $cell = $row->find('xpath', '//td['. $columnNum .']');
        $checkbox = $cell->find('css', 'input[type="checkbox"]');

        if (!isset($checkbox)) {
            throw new UnexpectedPageException(sprintf(
                'Can\'t find checkbox in %d column of %d row',
                $columnNum,
                $rowNum
            ));
        }

        $checkbox->check();
    }
}
