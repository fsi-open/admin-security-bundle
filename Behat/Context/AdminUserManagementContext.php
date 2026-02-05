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
use Behat\Mink\Driver\BrowserKitDriver;
use FriendsOfBehat\PageObjectExtension\Page\UnexpectedPageException;
use FriendsOfBehat\SymfonyExtension\Driver\SymfonyDriver;
use FSi\Bundle\AdminSecurityBundle\Behat\Element\BatchAction;
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
            foreach ($row as $key => $expectedValue) {
                $cellRowIndex = $rowIndex + 1;
                $cell = $datagrid->getCellByColumnName($key, $cellRowIndex);
                Assertion::notNull($cell, "No cell for \"{$key}\" and row \"{$cellRowIndex}\"");

                $actualValue = $cell->getText();
                Assertion::same(
                    $actualValue,
                    $expectedValue,
                    "Expected \"{$expectedValue}\" for cell \"{$key}\" in row {$rowIndex}, got \"{$actualValue}\"."
                );
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
        $batchActionsElement = $this->getBatchActionsElement();
        $batchActionNode = $batchActionsElement->findOptionForAction($action);

        $row = $this->getDatagrid()->getRow($cellIndex);
        $cell = $row->find('xpath', '//td[1]');
        Assertion::notNull($cell, "No cell in row \"{$cellIndex}\"");
        $checkbox = $cell->find('css', 'input[type="checkbox"]');

        if (null === $checkbox) {
            throw new UnexpectedPageException(
                "Can\'t find checkbox in first column of {$cellIndex} row"
            );
        }

        $data = [
            'batch_action' => [
                '_token' => $batchActionsElement->getCsrfToken()
            ],
            'indexes' => [
                $checkbox->getAttribute('value')
            ]
        ];

        /** @var SymfonyDriver $driver */
        $driver = $this->getSession()->getDriver();

        $batchActionUrl = $batchActionNode->getAttribute('value');
        Assertion::notNull($batchActionUrl, "No url for action \"{$action}\"");
        $driver->getClient()->request('POST', $batchActionUrl, $data);
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

    public function getBatchActionsElement(): BatchAction
    {
        /** @var BatchAction $datagrid */
        $datagrid = $this->getElement(BatchAction::class);
        return $datagrid;
    }
}
