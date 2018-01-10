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
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\Login;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;

class AdminUserContext extends PageObjectContext
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
    public function iMLoggedInAsAdmin()
    {
        $this->loginPage->open();
        $this->iFillFormWithValidAdminLoginAndPassword();
        $this->iPressButton('Login');
    }

    /**
     * @Given /^I\'m logged in as redactor$/
     */
    public function iMLoggedInAsRedactor()
    {
        $this->loginPage->open();
        $this->iFillFormWithValidRedactorLoginAndPassword();
        $this->iPressButton('Login');
    }

    /**
     * @When /^I fill form with login "([^"]*)" and password "([^"]*)"$/
     */
    public function iFillFormWithLoginAndPassword($username, $password)
    {
        $this->changePasswordPage->fillField('E-mail', $username);
        $this->changePasswordPage->fillField('Password', $password);
    }

    /**
     * @Then /^I should see login form with following fields:$/
     */
    public function iShouldSeeLoginFormWithFollowingFields(TableNode $fieldsTable)
    {
        foreach ($fieldsTable->getHash() as $fieldRow) {
            expect($this->loginPage->hasField($fieldRow['Field name']))->toBe(true);
        }
    }

    /**
     * @Given /^I should also see login form "([^"]*)" button$/
     */
    public function iShouldAlsoSeeLoginFormButton($buttonName)
    {
        expect($this->loginPage->hasButton($buttonName))->toBe(true);
    }

    /**
     * @When /^I fill form with valid admin login and password$/
     */
    public function iFillFormWithValidAdminLoginAndPassword()
    {
        $this->loginPage->fillField('E-mail', 'admin');
        $this->loginPage->fillField('Password', 'admin');
    }

    /**
     * @When /^I fill form with valid redactor login and password$/
     */
    public function iFillFormWithValidRedactorLoginAndPassword()
    {
        $this->loginPage->fillField('E-mail', 'redactor');
        $this->loginPage->fillField('Password', 'redactor');
    }

    /**
     * @When /^I fill form with invalid admin login and password$/
     */
    public function iFillFormWithInvalidAdminLoginAndPassword()
    {
        $this->loginPage->fillField('E-mail', 'invalid mail');
        $this->loginPage->fillField('Password', 'invalid password');
    }

    /**
     * @Given /^I press "([^"]*)" button$/
     */
    public function iPressButton($buttonName)
    {
        $this->loginPage->pressButton($buttonName);
    }

    /**
     * @Then /^I should see login form error message "([^"]*)"$/
     */
    public function iShouldSeeLoginFormErrorMessage($message)
    {
        expect($this->loginPage->getFormErrorMessage())->toBe($message);
    }

    /**
     * @Given /^I should see change password form with following fields$/
     */
    public function iShouldSeeChangePasswordFormWithFollowingFields(TableNode $formField)
    {
        foreach ($formField->getHash() as $fieldData) {
            expect($this->changePasswordPage->hasField($fieldData['Field name']))->toBe(true);
        }
    }

    /**
     * @Given /^I should see change password form "([^"]*)" button$/
     */
    public function iShouldSeeChangePasswordFormButton($buttonName)
    {
        expect($this->changePasswordPage->hasButton($buttonName))->toBe(true);
    }

    /**
     * @When /^I change my password$/
     */
    public function iChangeMyPassword()
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
    public function iFillChangePasswordFormFieldsWithValidData()
    {
        $this->changePasswordPage->fillField('Current password', 'admin');
        $this->changePasswordPage->fillField('New password', 'admin-new');
        $this->changePasswordPage->fillField('Repeat password', 'admin-new');
    }

    /**
     * @When /^I fill change password form fields with invalid current password$/
     */
    public function iFillChangePasswordFormFieldsWithInvalidCurrentPassword()
    {
        $this->changePasswordPage->fillField('Current password', 'invalid-password');
        $this->changePasswordPage->fillField('New password', 'admin-new');
        $this->changePasswordPage->fillField('Repeat password', 'admin-new');
    }

    /**
     * @When /^I fill change password form fields with repeat password different than new password$/
     */
    public function iFillChangePasswordFormFieldsWithRepeatPasswordDifferentThanNewPassword()
    {
        $this->changePasswordPage->fillField('Current password', 'admin');
        $this->changePasswordPage->fillField('New password', 'admin-new');
        $this->changePasswordPage->fillField('Repeat password', 'admin-new-different');
    }

    /**
     * @Given /^I press "([^"]*)"$/
     */
    public function iPress($button)
    {
        $this->changePasswordPage->pressButton($button);
    }

    /**
     * @Given /^I should see "([^"]*)" field error in change password form with message$/
     */
    public function iShouldSeeFieldErrorInChangePasswordFormWithMessage($field, PyStringNode $message)
    {
        expect($this->changePasswordPage->findFieldError($field)->getText())->toBe((string) $message);
    }

    /**
     * @Then /^I should see information about passwords mismatch$/
     */
    public function iShouldSeeInformationAboutPasswordsMismatch()
    {
        /** @var \FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\Element\Form $form */
        $form = $this->getElement('Form');
        expect($form->getFieldErrors('New password'))->toBe('The entered passwords don\'t match');
    }
}
