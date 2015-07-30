<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;

class AdminUserContext extends PageObjectContext
{
    /**
     * @Given /^I\'m logged in as admin$/
     */
    public function iMLoggedInAsAdmin()
    {
        $this->getPage('Login')->open();
        $this->iFillFormWithValidAdminLoginAndPassword();
        $this->iPressButton('Login');
    }

    /**
     * @Given /^I\'m logged in as redactor$/
     */
    public function iMLoggedInAsRedactor()
    {
        $this->getPage('Login')->open();
        $this->iFillFormWithValidRedactorLoginAndPassword();
        $this->iPressButton('Login');
    }

    /**
     * @When /^I fill form with login "([^"]*)" and password "([^"]*)"$/
     */
    public function iFillFormWithLoginAndPassword($username, $password)
    {
        $page = $this->getPage('Admin Change Password');
        $page->fillField('E-mail', $username);
        $page->fillField('Password', $password);
    }

    /**
     * @Then /^I should see login form with following fields:$/
     */
    public function iShouldSeeLoginFormWithFollowingFields(TableNode $fieldsTable)
    {
        foreach ($fieldsTable->getHash() as $fieldRow) {
            expect($this->getPage('Login')->hasField($fieldRow['Field name']))->toBe(true);
        }
    }

    /**
     * @Given /^I should also see login form "([^"]*)" button$/
     */
    public function iShouldAlsoSeeLoginFormButton($buttonName)
    {
        expect($this->getPage('Login')->hasButton($buttonName))->toBe(true);
    }

    /**
     * @When /^I fill form with valid admin login and password$/
     */
    public function iFillFormWithValidAdminLoginAndPassword()
    {
        $this->getPage('Login')->fillField('E-mail', 'admin');
        $this->getPage('Login')->fillField('Password', 'admin');
    }

    /**
     * @When /^I fill form with valid redactor login and password$/
     */
    public function iFillFormWithValidRedactorLoginAndPassword()
    {
        $this->getPage('Login')->fillField('E-mail', 'redactor');
        $this->getPage('Login')->fillField('Password', 'redactor');
    }

    /**
     * @When /^I fill form with invalid admin login and password$/
     */
    public function iFillFormWithInvalidAdminLoginAndPassword()
    {
        $this->getPage('Login')->fillField('E-mail', 'invalid mail');
        $this->getPage('Login')->fillField('Password', 'invalid password');
    }

    /**
     * @Given /^I press "([^"]*)" button$/
     */
    public function iPressButton($buttonName)
    {
        $this->getPage('Login')->pressButton($buttonName);
    }

    /**
     * @Then /^I should see login form error message "([^"]*)"$/
     */
    public function iShouldSeeLoginFormErrorMessage($message)
    {
        expect($this->getPage('Login')->getFormErrorMessage())->toBe($message);
    }

    /**
     * @Given /^I should see change password form with following fields$/
     */
    public function iShouldSeeChangePasswordFormWithFollowingFields(TableNode $formField)
    {
        foreach ($formField->getHash() as $fieldData) {
            expect($this->getPage('Admin change password')->hasField($fieldData['Field name']))->toBe(true);
        }
    }

    /**
     * @Given /^I should see change password form "([^"]*)" button$/
     */
    public function iShouldSeeChangePasswordFormButton($buttonName)
    {
        expect($this->getPage('Admin change password')->hasButton($buttonName))->toBe(true);
    }

    /**
     * @When /^I change my password$/
     */
    public function iChangeMyPassword()
    {
        $this->getPage('Admin change password')->open();
        $this->getPage('Admin change password')->fillField('Current password', 'redactor');
        $this->getPage('Admin change password')->fillField('New password', 'admin-new');
        $this->getPage('Admin change password')->fillField('Repeat password', 'admin-new');
        $this->iPress('Save');
    }

    /**
     * @When /^I fill change password form fields with valid data$/
     */
    public function iFillChangePasswordFormFieldsWithValidData()
    {
        $this->getPage('Admin change password')->fillField('Current password', 'admin');
        $this->getPage('Admin change password')->fillField('New password', 'admin-new');
        $this->getPage('Admin change password')->fillField('Repeat password', 'admin-new');
    }

    /**
     * @When /^I fill change password form fields with invalid current password$/
     */
    public function iFillChangePasswordFormFieldsWithInvalidCurrentPassword()
    {
        $this->getPage('Admin change password')->fillField('Current password', 'invalid-password');
        $this->getPage('Admin change password')->fillField('New password', 'admin-new');
        $this->getPage('Admin change password')->fillField('Repeat password', 'admin-new');
    }

    /**
     * @When /^I fill change password form fields with repeat password different than new password$/
     */
    public function iFillChangePasswordFormFieldsWithRepeatPasswordDifferentThanNewPassword()
    {
        $this->getPage('Admin change password')->fillField('Current password', 'admin');
        $this->getPage('Admin change password')->fillField('New password', 'admin-new');
        $this->getPage('Admin change password')->fillField('Repeat password', 'admin-new-different');
    }

    /**
     * @Given /^I press "([^"]*)"$/
     */
    public function iPress($button)
    {
        $this->getPage('Admin change password')->pressButton($button);
    }

    /**
     * @Given /^I should see "([^"]*)" field error in change password form with message$/
     */
    public function iShouldSeeFieldErrorInChangePasswordFormWithMessage($field, PyStringNode $message)
    {
        expect($this->getPage('Admin change password')->findFieldError($field)->getText())->toBe((string) $message);
    }

    /**
     * @Then /^I should see information about passwords mismatch$/
     */
    public function iShouldSeeInformationAboutPasswordsMismatch()
    {
        /** @var \FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\Element\Form $form */
        $form = $this->getElement('Form');
        expect($form->getFieldErrors('New password'))->toBe('This value is not valid.');
    }
}
