<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Element;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Element\Element;
use FriendsOfBehat\PageObjectExtension\Page\UnexpectedPageException;

use function count;

final class Datagrid extends Element
{
    /**
     * @param int $number
     * @return NodeElement
     */
    public function getRow(int $number): NodeElement
    {
        $row = $this->getElement('datagrid')->find('xpath', "//tbody/tr[{$number}]");
        if (null === $row) {
            throw new UnexpectedPageException("Row \"{$number}\" does not exist in DataGrid");
        }

        return $row;
    }

    public function getRowCount(): int
    {
        $records = $this->getElement('datagrid')->findAll('css', 'tbody > tr');
        return count($records);
    }

    public function checkCellCheckbox(int $rowNumber, int $columnNumber = 1): void
    {
        $row = $this->getRow($rowNumber);
        $cell = $row->find('xpath', "//td[{$columnNumber}]");
        if (null === $cell) {
            throw new UnexpectedPageException(
                "No cell for numer {$columnNumber} and row {$rowNumber}"
            );
        }

        $checkbox = $cell->find('css', 'input[type="checkbox"]');
        if (null === $checkbox) {
            throw new UnexpectedPageException(sprintf(
                'Can\'t find checkbox in %d column of %d row',
                $columnNumber,
                $rowNumber
            ));
        }

        $checkbox->check();
    }

    public function getCellByColumnName(string $columnName, int $rowNum): ?NodeElement
    {
        $row = $this->getRow($rowNum);
        $pos = $this->getColumnPosition($columnName);

        return $row->find('xpath', '//td[' . $pos . ']');
    }

    /**
     * @return array<string, string>
     */
    protected function getDefinedElements(): array
    {
        return [
            'datagrid' => '.table-datagrid'
        ];
    }

    private function getColumnPosition(string $columnHeader, bool $throwIfNotFound = true): ?int
    {
        $columns = $this->getElement('datagrid')->findAll('xpath', "//thead/tr/th");

        /** @var NodeElement $columnElement */
        foreach ($columns as $index => $columnElement) {
            $title = $columnElement->find('xpath', "/span[contains(text(), \"{$columnHeader}\")]");
            if (null !== $title) {
                return $index + 1;
            }
        }

        if (true === $throwIfNotFound) {
            throw new UnexpectedPageException(sprintf(
                'Column with title "%s" does not exist in DataGrid at page "%s".',
                $columnHeader,
                $this->getDriver()->getCurrentUrl()
            ));
        }

        return null;
    }
}
