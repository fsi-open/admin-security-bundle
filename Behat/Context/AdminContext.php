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
use Behat\Mink\Mink;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\ORM\Tools\SchemaTool;
use FSi\FixturesBundle\Entity\User;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Symfony\Component\HttpKernel\KernelInterface;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;

class AdminContext extends PageObjectContext implements KernelAwareContext, MinkAwareContext
{
    /**
     * @var Mink
     */
    private $mink;

    /**
     * @var array
     */
    private $minkParameters;

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
     * {@inheritdoc}
     */
    public function setMink(Mink $mink)
    {
        $this->mink = $mink;
    }

    /**
     * @return Mink
     */
    public function getMink()
    {
        return $this->mink;
    }

    /**
     * {@inheritdoc}
     */
    public function setMinkParameters(array $parameters)
    {
        $this->minkParameters = $parameters;
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
     * @Then /^I should be redirected to "([^"]*)" page$/
     */
    public function iShouldBeRedirectedToPage($pageName)
    {
        expect($this->getPage($pageName)->isOpen())->toBe(true);
    }

    /**
     * @Given /^I should see message "([^"]*)"$/
     */
    public function iShouldSeeMessage($message)
    {
        expect($this->getElement('FlashMessage')->getText())->toBe($message);
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
     * @Then /^i should see (\d+) error$/
     */
    public function iShouldSeeHttpError($httpStatusCode)
    {
        expect($this->mink->getSession()->getStatusCode())->toBe(intval($httpStatusCode));
    }

    /**
     * @Then /^I should see page header with "([^"]*)" content$/
     */
    public function iShouldSeePageHeaderWithContent($headerText)
    {
        expect($this->getElement('Page Header')->getText())->toBe($headerText);
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
}
