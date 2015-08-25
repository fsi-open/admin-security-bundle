<?php

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\UserList;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;

class AdminUserManagementContext extends PageObjectContext
{
    /**
     * @Then I should see following table:
     */
    public function iShouldSeeTable(TableNode $table)
    {
        expect($this->getUserListPage()->getUsersCount())->toBe(count($table->getHash()));

        // FIXME: more checks!
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
    public function iDeleteFirstUserOnTheList()
    {
        $page = $this->getUserListPage();
        $page->getBatchActionsElement()->selectOption('admin.user_list.batch_action.delete');
        $page->checkCellCheckbox(2);
        $page->pressButton('Ok');
    }

    /**
     * @When I reset password for the second user on the list
     */
    public function iResetPasswordForTheSecondUserOnTheList()
    {
        $page = $this->getUserListPage();
        $page->getBatchActionsElement()->selectOption('admin.user_list.batch_action.password_reset');
        $page->checkCellCheckbox(2);
        $page->pressButton('Ok');
    }


    /**
     * @return UserList
     */
    private function getUserListPage()
    {
        return $this->getPage('UserList');
    }
}
