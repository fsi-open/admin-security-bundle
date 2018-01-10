<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

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

    public function getRowCount(): int
    {
        $records = $this->findAll('css', 'tbody > tr');

        return count($records);
    }

    public function checkCellCheckbox(int $rowNum, int $columnNum = 1): void
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

    public function getCellByColumnName(string $columnName, int $rowNum): ?NodeElement
    {
        $row = $this->getRow($rowNum);
        $pos = $this->getColumnPosition($columnName);

        return $row->find('xpath', '//td['. $pos .']');
    }

    private function getColumnPosition(string $columnHeader, bool $throwIfNotFound = true): ?int
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
