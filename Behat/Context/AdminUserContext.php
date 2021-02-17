<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\AdminChangePassword;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\Element\Form;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\Login;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;

final class AdminUserContext extends PageObjectContext
{
    /**
     * @var Login
     */
    private $loginPage;

    /**
     * @var AdminChangePassword
     */
    private $changePasswordPage;

    public function __construct(Login $loginPage, AdminChangePassword $changePasswordPage)
    {
        $this->loginPage = $loginPage;
        $this->changePasswordPage = $changePasswordPage;
    }

    /**
     * @Given /^I\'m logged in as admin$/
     */
    public function iMLoggedInAsAdmin(): void
    {
        if (false === $this->loginPage->getSession()->isStarted()) {
            $this->loginPage->getSession()->start();
        }
        $this->loginPage->open();
        $this->iFillFormWithValidAdminLoginAndPassword();
        $this->iPressButton('Login');
    }

    /**
     * @Given /^I\'m logged in as redactor$/
     */
    public function iMLoggedInAsRedactor(): void
    {
        $this->loginPage->open();
        $this->iFillFormWithValidRedactorLoginAndPassword();
        $this->iPressButton('Login');
    }

    /**
     * @When /^I fill form with login "([^"]*)" and password "([^"]*)"$/
     */
    public function iFillFormWithLoginAndPassword(string $username, string $password): void
    {
        $this->changePasswordPage->fillField('E-mail', $username);
        $this->changePasswordPage->fillField('Password', $password);
    }

    /**
     * @Then /^I should see login form with following fields:$/
     */
    public function iShouldSeeLoginFormWithFollowingFields(TableNode $fieldsTable): void
    {
        foreach ($fieldsTable->getHash() as $fieldRow) {
            expect($this->loginPage->hasField($fieldRow['Field name']))->toBe(true);
        }
    }

    /**
     * @Given /^I should also see login form "([^"]*)" button$/
     */
    public function iShouldAlsoSeeLoginFormButton(string $buttonName): void
    {
        expect($this->loginPage->hasButton($buttonName))->toBe(true);
    }

    /**
     * @When /^I fill form with valid admin login and password$/
     */
    public function iFillFormWithValidAdminLoginAndPassword(): void
    {
        $this->loginPage->fillField('E-mail', 'admin');
        $this->loginPage->fillField('Password', 'admin');
    }

    /**
     * @When /^I fill form with valid redactor login and password$/
     */
    public function iFillFormWithValidRedactorLoginAndPassword(): void
    {
        $this->loginPage->fillField('E-mail', 'redactor');
        $this->loginPage->fillField('Password', 'redactor');
    }

    /**
     * @When /^I fill form with invalid admin login and password$/
     */
    public function iFillFormWithInvalidAdminLoginAndPassword(): void
    {
        $this->loginPage->fillField('E-mail', 'invalid mail');
        $this->loginPage->fillField('Password', 'invalid password');
    }

    /**
     * @Given /^I press "([^"]*)" button$/
     */
    public function iPressButton(string $buttonName): void
    {
        $this->loginPage->pressButton($buttonName);
    }

    /**
     * @Then /^I should see login form error message "([^"]*)"$/
     */
    public function iShouldSeeLoginFormErrorMessage(string $message): void
    {
        expect($this->loginPage->getFormErrorMessage())->toBe($message);
    }

    /**
     * @Given /^I should see change password form with following fields$/
     */
    public function iShouldSeeChangePasswordFormWithFollowingFields(TableNode $formField): void
    {
        foreach ($formField->getHash() as $fieldData) {
            expect($this->changePasswordPage->hasField($fieldData['Field name']))->toBe(true);
        }
    }

    /**
     * @Given /^I should see change password form "([^"]*)" button$/
     */
    public function iShouldSeeChangePasswordFormButton(string $buttonName): void
    {
        expect($this->changePasswordPage->hasButton($buttonName))->toBe(true);
    }

    /**
     * @When /^I change my password$/
     */
    public function iChangeMyPassword(): void
    {
        $this->changePasswordPage->open();
        $this->changePasswordPage->fillField('Current password', 'redactor');
        $this->changePasswordPage->fillField('New password', 'admin-new');
        $this->changePasswordPage->fillField('Repeat password', 'admin-new');
        $this->iPress('Save');
    }

    /**
     * @When /^I fill change password form fields with valid data$/
     */
    public function iFillChangePasswordFormFieldsWithValidData(): void
    {
        $this->changePasswordPage->fillField('Current password', 'admin');
        $this->changePasswordPage->fillField('New password', 'admin-new');
        $this->changePasswordPage->fillField('Repeat password', 'admin-new');
    }

    /**
     * @When /^I fill change password form fields with invalid current password$/
     */
    public function iFillChangePasswordFormFieldsWithInvalidCurrentPassword(): void
    {
        $this->changePasswordPage->fillField('Current password', 'invalid-password');
        $this->changePasswordPage->fillField('New password', 'admin-new');
        $this->changePasswordPage->fillField('Repeat password', 'admin-new');
    }

    /**
     * @When /^I fill change password form fields with repeat password different than new password$/
     */
    public function iFillChangePasswordFormFieldsWithRepeatPasswordDifferentThanNewPassword(): void
    {
        $this->changePasswordPage->fillField('Current password', 'admin');
        $this->changePasswordPage->fillField('New password', 'admin-new');
        $this->changePasswordPage->fillField('Repeat password', 'admin-new-different');
    }

    /**
     * @Given /^I press "([^"]*)"$/
     */
    public function iPress(string $button): void
    {
        $this->changePasswordPage->pressButton($button);
    }

    /**
     * @Given /^I should see "([^"]*)" field error in change password form with message$/
     */
    public function iShouldSeeFieldErrorInChangePasswordFormWithMessage(string $field, PyStringNode $message): void
    {
        expect($this->changePasswordPage->findFieldError($field)->getText())->toBe((string) $message);
    }

    /**
     * @Then /^I should see information about passwords mismatch$/
     */
    public function iShouldSeeInformationAboutPasswordsMismatch(): void
    {
        /** @var Form $form */
        $form = $this->getElement('Form');
        expect($form->getFieldErrors('New password'))->toBe('The entered passwords don\'t match');
    }
}
