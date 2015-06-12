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
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\ORM\Tools\SchemaTool;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Symfony\Component\HttpKernel\KernelInterface;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;

class AdminUserContext extends PageObjectContext implements KernelAwareContext
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @BeforeScenario
     */
    public function createDatabase()
    {
        $this->deleteDatabaseIfExist();
        $metadata = $this->getDoctrine()->getManager()->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($this->getDoctrine()->getManager());
        $tool->createSchema($metadata);
    }

    /**
     * @Given /^there is "([^"]*)" user with role "([^"]*)" and password "([^"]*)"$/
     */
    public function thereIsUserWithRoleAndPassword($nick, $role, $password)
    {
        /** @var \FOS\UserBundle\Doctrine\UserManager $userManager */
        $userManager = $this->kernel->getContainer()->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $user->setUsername($nick)
            ->setEmail($nick)
            ->setRoles(array($role))
            ->setPlainPassword($password)
            ->setEnabled(true);

        $userManager->updateUser($user);
    }

    /**
     * @AfterScenario
     */
    public function deleteDatabaseIfExist()
    {
        $dbFilePath = $this->kernel->getRootDir() . '/data.sqlite';

        if (file_exists($dbFilePath)) {
            unlink($dbFilePath);
        }
    }

    /**
     * @Given /^I am on the "([^"]*)" page$/
     */
    public function iAmOnThePage($pageName)
    {
        $this->getPage($pageName)->open();
    }


    /**
     * @Given /^I\'m not logged in$/
     */
    public function iMNotLoggedIn()
    {
        expect($this->kernel->getContainer()->get('security.context')->getToken())->toBe(null);
    }

    /**
     * @Given /^I\'m logged in as admin$/
     */
    public function iMLoggedInAsAdmin()
    {
        $this->iMNotLoggedIn();
        $this->iAmOnThePage('Login');
        $this->iFillFormWithValidAdminLoginAndPassword();
        $this->iPressButton('Login');
    }

    /**
     * @Given /^I\'m logged in as redactor$/
     */
    public function iMLoggedInAsRedactor()
    {
        $this->iMNotLoggedIn();
        $this->iAmOnThePage('Login');
        $this->iFillFormWithValidRedactorLoginAndPassword();
        $this->iPressButton('Login');
    }

    /**
     * @When /^I open "([^"]*)" page$/
     */
    public function iOpenPage($pageName)
    {
        $this->getPage($pageName)->open();
    }

    /**
     * @When /^I try to open "([^"]*)" page$/
     */
    public function iTryToOpenPage($pageName)
    {
        try {
            $this->iOpenPage($pageName);
        } catch (UnexpectedPageException $e) {
            // probably it is redirect
        }
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
     * @Then /^I should be redirected to "([^"]*)" page$/
     */
    public function iShouldBeRedirectedToPage($pageName)
    {
        $this->getPage($pageName)->isOpen();
    }

    /**
     * @Then /^I should see login form error message "([^"]*)"$/
     */
    public function iShouldSeeLoginFormErrorMessage($message)
    {
        expect($this->getPage('Login')->getFormErrorMessage())->toBe($message);
    }

    /**
     * @Given /^I should see message "([^"]*)"$/
     */
    public function iShouldSeeMessage($message)
    {
        expect($this->getPage('Login')->getFormSuccessMessage())->toBe($message);
    }

    /**
     * @Then /^I should see dropdown button in navigation bar "([^"]*)"$/
     */
    public function iShouldSeeDropdownButtonInNavigationBar($buttonText)
    {
        expect($this->getPage('Admin panel')->hasLink($buttonText))->toBe(true);
    }

    /**
     * @Given /^"([^"]*)" dropdown button should have following links$/
     */
    public function dropdownButtonShouldHaveFollowingLinks($button, TableNode $dropdownLinks)
    {
        $links = $this->getPage('Admin panel')->getDropdownOptions($button);

        foreach ($dropdownLinks->getHash() as $link) {
            expect($links)->toContain($link['Link']);
        }
    }

    /**
     * @When /^I click "([^"]*)" link from "([^"]*)" dropdown button$/
     */
    public function iClickLinkFromDropdownButton($link, $dropdown)
    {
        $this->getPage('Admin panel')->getDropdown($dropdown)->clickLink($link);
    }

    /**
     * @Then /^I should be logged off$/
     */
    public function iShouldBeLoggedOff()
    {
        expect($this->kernel->getContainer()->get('security.context')->getToken())->toBe(null);
    }

    /**
     * @Then /^I should see page header with "([^"]*)" content$/
     */
    public function iShouldSeePageHeaderWithContent($headerText)
    {
        expect($this->getElement('Page Header')->getText())->toBe($headerText);
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
     * @Then /^user password should be changed$/
     */
    public function userPasswordShouldBeChanged()
    {
        $user = $this->getDoctrine()->getManager()->getRepository('FSi\FixturesBundle\Entity\User')
            ->findOneBy(array('username' => 'admin'));

        $encoder = $this->kernel->getContainer()->get('security.encoder_factory')
            ->getEncoder($user);

        $encodedPassword = $encoder->encodePassword('admin-new', $user->getSalt());

        expect($user->getPassword())->toBe($encodedPassword);
    }

    /**
     * @Then /^I should see navigation menu with following elements$/
     */
    public function iShouldSeeNavigationMenuWithFollowingElements(TableNode $menu)
    {
        foreach($menu->getHash() as $elementData) {
            expect($this->getPage('Admin Panel')->hasElementInTopMenu($elementData['Element']))
                ->toBe(true);
        }
    }

    /**
     * @Given /^I should not see "([^"]*)" position in menu$/
     */
    public function iShouldNotSeePositionInMenu($elementName)
    {
        expect($this->getPage('Admin Panel')->hasElementInTopMenu($elementName))
            ->toBe(false);
    }

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected function getDoctrine()
    {
        return $this->kernel->getContainer()->get('doctrine');
    }
}
