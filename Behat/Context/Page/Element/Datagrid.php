<?php

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\Element;

use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;

class Datagrid extends Element
{
    protected $selector = '.table-datagrid';

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
     * @return int
     */
    public function getRowCount()
    {
        $records = $this->findAll('css', 'tbody > tr');

        return count($records);
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

    /**
     * @param string $columnName
     * @param int $rowNum
     * @return NodeElement|null
     */
    public function getCellByColumnName($columnName, $rowNum)
    {
        $row = $this->getRow($rowNum);
        $pos = $this->getColumnPosition($columnName);

        return $row->find('xpath', '//td['. $pos .']');
    }

    /**
     * @param string $columnHeader
     * @return int
     */
    private function getColumnPosition($columnHeader, $throwIfNotFound = true)
    {
        $columns = $this->findAll('xpath', "//thead/tr/th");

        foreach ($columns as $index => $columnElement) {
            /** @var NodeElement $columnElement */
            $title = $columnElement->find('xpath', '/span[contains(text(),"' . $columnHeader . '")]');
            if (isset($title)) {
                return $index + 1;
            }
        }

        if ($throwIfNotFound) {
            throw new UnexpectedPageException(sprintf(
                'Column with title "%s" does not exist in DataGrid at page "%s".',
                $columnHeader,
                $this->getName()
            ));
        }

        return null;
    }
}
