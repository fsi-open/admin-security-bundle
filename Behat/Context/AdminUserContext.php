<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Symfony\Component\HttpKernel\KernelInterface;

class AdminUserContext extends PageObjectContext implements KernelAwareInterface
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel HttpKernel instance
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given /^I on the "([^"]*)" page$/
     */
    public function iOnThePage($pageName)
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
     * @When /^I open "([^"]*)" page$/
     */
    public function iOpenPage($pageName)
    {
        $this->getPage($pageName)->open();
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
}