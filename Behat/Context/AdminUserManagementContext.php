<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Assert\Assertion;
use Behat\Gherkin\Node\TableNode;
use FSi\Bundle\AdminSecurityBundle\Behat\Element\Datagrid;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\UserList;

use function count;

final class AdminUserManagementContext extends AbstractContext
{
    /**
     * @Then I should see following table:
     */
    public function iShouldSeeTable(TableNode $table): void
    {
        $datagrid = $this->getDatagrid();

        Assertion::same($datagrid->getRowCount(), count($table->getHash()));
        foreach ($table->getHash() as $rowIndex => $row) {
            foreach ($row as $key => $value) {
                $cell = $datagrid->getCellByColumnName($key, $rowIndex + 1);
                Assertion::same($value, $cell->getText());
            }
        }
    }

    /**
     * @Then I should have following list batch actions:
     */
    public function iShouldHaveFollowingListBatchActions(TableNode $table): void
    {
        Assertion::same(
            array_values($this->getUserListPage()->getBatchActions()),
            array_keys($table->getRowsHash())
        );
    }

    /**
     * @When I delete second user on the list
     */
    public function iDeleteSecondUserOnTheList(): void
    {
        $this->performBatchAction('Delete', 2);
    }

    /**
     * @When I reset password for the second user on the list
     */
    public function iResetPasswordForTheSecondUserOnTheList(): void
    {
        $this->performBatchAction('Reset password', 2);
    }

    /**
     * @When I resend activation token to the second user on the list
     */
    public function iResendActivationTokenToTheFirstUserOnTheList(): void
    {
        $this->performBatchAction('Resend activation token', 2);
    }

    /**
     * @When I click :name link
     */
    public function iPressLink(string $name): void
    {
        $this->getUserListPage()->clickLink($name);
    }

    /**
     * @Then I fill form with valid user data
     */
    public function iFillFormWithValidUserData(): void
    {
        $userListPage = $this->getUserListPage();
        $userListPage->fillField('Email', 'new-user@fsi.pl');
        $userListPage->checkField('ROLE_ADMIN');
    }

    private function performBatchAction(string $action, int $cellIndex): void
    {
        $userListPage = $this->getUserListPage();
        $userListPage->getBatchActionsElement()->selectOption($action);

        $this->getDatagrid()->checkCellCheckbox($cellIndex);

        $userListPage->pressButton('Ok');
    }

    private function getUserListPage(): UserList
    {
        return $this->getPageObject(UserList::class);
    }

    private function getDatagrid(): Datagrid
    {
        /** @var Datagrid $datagrid */
        $datagrid = $this->getElement(Datagrid::class);
        return $datagrid;
    }
}
