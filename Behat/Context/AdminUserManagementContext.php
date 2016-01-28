<?php

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\Element\Datagrid;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\UserList;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;

class AdminUserManagementContext extends PageObjectContext
{
    /**
     * @Then I should see following table:
     */
    public function iShouldSeeTable(TableNode $table)
    {
        $datagrid = $this->getDatagrid();

        expect($datagrid->getRowCount())->toBe(count($table->getHash()));

        foreach ($table->getHash() as $rowIndex => $row) {

            foreach ($row as $key => $value) {
                $cell = $datagrid->getCellByColumnName($key, $rowIndex + 1);

                expect($cell->getText())->toBe($value);
            }
        }
    }

    /**
     * @Then I should have following list batch actions:
     */
    public function iShouldHaveFollowingListBatchActions(TableNode $table)
    {
        $page = $this->getUserListPage();

        expect(array_values($page->getBatchActions()))->toBe(array_keys($table->getRowsHash()));
    }

    /**
     * @When I delete second user on the list
     */
    public function iDeleteSecondUserOnTheList()
    {
        $page = $this->getUserListPage();
        $page->getBatchActionsElement()->selectOption('Delete');

        $datagrid = $this->getDatagrid();
        $datagrid->checkCellCheckbox(2);

        $page->pressButton('Ok');
    }

    /**
     * @When I reset password for the second user on the list
     */
    public function iResetPasswordForTheSecondUserOnTheList()
    {
        $page = $this->getUserListPage();
        $page->getBatchActionsElement()->selectOption('Reset password');

        $datagrid = $this->getDatagrid();
        $datagrid->checkCellCheckbox(2);

        $page->pressButton('Ok');
    }

    /**
     * @When I click :name link
     */
    public function iPressLink($name)
    {
        $page = $this->getUserListPage();
        $page->clickLink($name);
    }

    /**
     * @Then I fill form with valid user data
     */
    public function iFillFormWithValidUserData()
    {
        $page = $this->getUserListPage();
        $page->fillField('Email', 'new-user@fsi.pl');
        $page->checkField('ROLE_ADMIN');
    }

    /**
     * @return UserList
     */
    private function getUserListPage()
    {
        return $this->getPage('UserList');
    }

    /**
     * @return Datagrid
     */
    private function getDatagrid()
    {
        return $this->getElement('Datagrid');
    }
}
