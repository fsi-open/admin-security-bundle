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
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use FSi\Bundle\AdminSecurityBundle\Behat\Element\Form;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\AdminChangePassword;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\Login;

final class AdminUserContext extends AbstractContext
{
    /**
     * @Given /^I\'m logged in as admin$/
     */
    public function iMLoggedInAsAdmin(): void
    {
        if (false === $this->getSession()->isStarted()) {
            $this->getSession()->start();
        }

        $this->getLoginPage()->open();
        $this->iFillFormWithValidAdminLoginAndPassword();
        $this->iPressButton('Login');
    }

    /**
     * @Given /^I\'m logged in as redactor$/
     */
    public function iMLoggedInAsRedactor(): void
    {
        $this->getLoginPage()->open();
        $this->iFillFormWithValidRedactorLoginAndPassword();
        $this->iPressButton('Login');
    }

    /**
     * @When /^I fill form with login "([^"]*)" and password "([^"]*)"$/
     */
    public function iFillFormWithLoginAndPassword(string $username, string $password): void
    {
        $this->getChangePasswordPage()->fillField('E-mail', $username);
        $this->getChangePasswordPage()->fillField('Password', $password);
    }

    /**
     * @Then /^I should see login form with following fields:$/
     */
    public function iShouldSeeLoginFormWithFollowingFields(TableNode $fieldsTable): void
    {
        $loginPage = $this->getLoginPage();
        foreach ($fieldsTable->getHash() as $fieldRow) {
            $fieldName = $fieldRow['Field name'];
            Assertion::true($loginPage->hasField($fieldName), "Field \"{$fieldName}\" not found.");
        }
    }

    /**
     * @Given /^I should also see login form "([^"]*)" button$/
     */
    public function iShouldAlsoSeeLoginFormButton(string $buttonName): void
    {
        Assertion::true(
            $this->getLoginPage()->hasButton($buttonName),
            "Button \"{$buttonName}\" not found."
        );
    }

    /**
     * @When /^I fill form with valid admin login and password$/
     */
    public function iFillFormWithValidAdminLoginAndPassword(): void
    {
        $loginPage = $this->getLoginPage();
        $loginPage->fillField('E-mail', 'admin@fsi.pl');
        $loginPage->fillField('Password', 'admin');
    }

    /**
     * @When /^I fill form with valid redactor login and password$/
     */
    public function iFillFormWithValidRedactorLoginAndPassword(): void
    {
        $loginPage = $this->getLoginPage();
        $loginPage->fillField('E-mail', 'redactor@fsi.pl');
        $loginPage->fillField('Password', 'redactor');
    }

    /**
     * @When /^I fill form with invalid admin login and password$/
     */
    public function iFillFormWithInvalidAdminLoginAndPassword(): void
    {
        $loginPage = $this->getLoginPage();
        $loginPage->fillField('E-mail', 'invalid mail');
        $loginPage->fillField('Password', 'invalid password');
    }

    /**
     * @Given /^I press "([^"]*)" button$/
     */
    public function iPressButton(string $buttonName): void
    {
        $this->getLoginPage()->pressButton($buttonName);
    }

    /**
     * @Then /^I should see login form error message "([^"]*)"$/
     */
    public function iShouldSeeLoginFormErrorMessage(string $message): void
    {
        Assertion::same(
            $this->getLoginPage()->getFormErrorMessage(),
            $message
        );
    }

    /**
     * @Given /^I should see change password form with following fields$/
     */
    public function iShouldSeeChangePasswordFormWithFollowingFields(TableNode $formField): void
    {
        $changePasswordPage = $this->getChangePasswordPage();
        foreach ($formField->getHash() as $fieldData) {
            $fieldName = $fieldData['Field name'];
            Assertion::true(
                $changePasswordPage->hasField($fieldName),
                "Field \"{$fieldName}\" not found."
            );
        }
    }

    /**
     * @Given /^I should see change password form "([^"]*)" button$/
     */
    public function iShouldSeeChangePasswordFormButton(string $buttonName): void
    {
        Assertion::true(
            $this->getChangePasswordPage()->hasButton($buttonName),
            "Button \"{$buttonName}\" not found."
        );
    }

    /**
     * @When /^I change my password$/
     */
    public function iChangeMyPassword(): void
    {
        $changePasswordPage = $this->getChangePasswordPage();
        $changePasswordPage->open();
        $changePasswordPage->fillField('Current password', 'redactor');
        $changePasswordPage->fillField('New password', 'admin-new');
        $changePasswordPage->fillField('Repeat password', 'admin-new');
        $this->iPress('Save');
    }

    /**
     * @When /^I fill change password form fields with valid data$/
     */
    public function iFillChangePasswordFormFieldsWithValidData(): void
    {
        $changePasswordPage = $this->getChangePasswordPage();
        $changePasswordPage->fillField('Current password', 'admin');
        $changePasswordPage->fillField('New password', 'admin-new');
        $changePasswordPage->fillField('Repeat password', 'admin-new');
    }

    /**
     * @When /^I fill change password form fields with invalid current password$/
     */
    public function iFillChangePasswordFormFieldsWithInvalidCurrentPassword(): void
    {
        $changePasswordPage = $this->getChangePasswordPage();
        $changePasswordPage->fillField('Current password', 'invalid-password');
        $changePasswordPage->fillField('New password', 'admin-new');
        $changePasswordPage->fillField('Repeat password', 'admin-new');
    }

    /**
     * @When /^I fill change password form fields with repeat password different than new password$/
     */
    public function iFillChangePasswordFormFieldsWithRepeatPasswordDifferentThanNewPassword(): void
    {
        $changePasswordPage = $this->getChangePasswordPage();
        $changePasswordPage->fillField('Current password', 'admin');
        $changePasswordPage->fillField('New password', 'admin-new');
        $changePasswordPage->fillField('Repeat password', 'admin-new-different');
    }

    /**
     * @Given /^I press "([^"]*)"$/
     */
    public function iPress(string $button): void
    {
        $this->getChangePasswordPage()->pressButton($button);
    }

    /**
     * @Given /^I should see "([^"]*)" field error in change password form with message$/
     */
    public function iShouldSeeFieldErrorInChangePasswordFormWithMessage(string $field, PyStringNode $message): void
    {
        $fieldError = $this->getChangePasswordPage()->findFieldError($field);
        Assertion::notNull($fieldError, "No error for field \"{$field}\".");
        Assertion::same($fieldError->getText(), (string) $message);
    }

    /**
     * @Then /^I should see information about passwords mismatch$/
     */
    public function iShouldSeeInformationAboutPasswordsMismatch(): void
    {
        /** @var Form $form */
        $form = $this->getElement(Form::class);
        Assertion::same(
            $form->getFieldErrors('New password'),
            'The entered passwords don\'t match'
        );
    }

    private function getLoginPage(): Login
    {
        return $this->getPageObject(Login::class);
    }

    private function getChangePasswordPage(): AdminChangePassword
    {
        return $this->getPageObject(AdminChangePassword::class);
    }
}
