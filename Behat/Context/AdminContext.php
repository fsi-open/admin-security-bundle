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
use Behat\Mink\Mink;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Exception;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\AdminChangePassword;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\AdminPanel;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\Login;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\PasswordResetRequest;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\UserList;
use InvalidArgumentException;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\UnexpectedPageException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Symfony\Component\HttpKernel\KernelInterface;

final class AdminContext extends PageObjectContext implements KernelAwareContext, MinkAwareContext
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
     * @var Login
     */
    private $loginPage;

    /**
     * @var AdminPanel
     */
    private $adminPanelPage;

    /**
     * @var AdminChangePassword
     */
    private $changePasswordPage;

    /**
     * @var PasswordResetRequest
     */
    private $passwordResetRequestPage;

    /**
     * @var UserList
     */
    private $userListPage;

    public function __construct(
        Login $loginPage,
        AdminPanel $adminPanelPage,
        AdminChangePassword $changePasswordPage,
        PasswordResetRequest $passwordResetRequestPage,
        UserList $userListPage
    ) {
        $this->loginPage = $loginPage;
        $this->adminPanelPage = $adminPanelPage;
        $this->changePasswordPage = $changePasswordPage;
        $this->passwordResetRequestPage = $passwordResetRequestPage;
        $this->userListPage = $userListPage;
    }

    public function setKernel(KernelInterface $kernel): void
    {
        $this->kernel = $kernel;
    }

    public function setMink(Mink $mink): void
    {
        $this->mink = $mink;
    }

    public function getMink(): Mink
    {
        return $this->mink;
    }

    public function setMinkParameters(array $parameters): void
    {
        $this->minkParameters = $parameters;
    }

    /**
     * @Given /^I am on the "([^"]*)" page$/
     */
    public function iAmOnThePage(string $pageName): void
    {
        $this->getPage($pageName)->open();
    }


    /**
     * @Given /^I\'m not logged in$/
     */
    public function iMNotLoggedIn(): void
    {
        $token = $this->kernel->getContainer()->get('security.token_storage')->getToken();
        if (null !== $token) {
            throw new Exception('User is logged in, though he is not suppose to be!');
        }
    }

    /**
     * @When I impersonate user :user
     */
    public function iImpersonateUser(string $user): void
    {
        $this->getMink()->getSession()->visit(
            sprintf('%s/admin/?_switch_user=%s', $this->minkParameters['base_url'], $user)
        );
    }

    /**
     * @Then I should be logged in as :user
     */
    public function iShouldBeLoggedInAs(string $user): void
    {
        expect($this->kernel->getContainer()->get('security.token_storage')->getToken()->getUsername())->toBe($user);
    }

    /**
     * @When /^I open "([^"]*)" page$/
     */
    public function iOpenPage(string $pageName): void
    {
        $this->getPage($pageName)->open();
    }

    /**
     * @When /^I try to open "([^"]*)" page$/
     */
    public function iTryToOpenPage(string $pageName): void
    {
        try {
            $this->iOpenPage($pageName);
        } catch (UnexpectedPageException $e) {
            // it probably is a redirect
        }
    }

    /**
     * @Then /^I should be redirected to "([^"]*)" page$/
     */
    public function iShouldBeRedirectedToPage(string $pageName): void
    {
        expect($this->getPage($pageName)->isOpen())->toBe(true);
    }

    /**
     * @Given /^I should see message:$/
     */
    public function iShouldSeeMessage(PyStringNode $message): void
    {
        expect($this->getElement('FlashMessage')->getText())->toBe($message->getRaw());
    }

    /**
     * @Then /^I should see dropdown button in navigation bar "([^"]*)"$/
     */
    public function iShouldSeeDropdownButtonInNavigationBar(string $buttonText): void
    {
        expect($this->adminPanelPage->hasLink($buttonText))->toBe(true);
    }

    /**
     * @Given /^"([^"]*)" dropdown button should have following links$/
     */
    public function dropdownButtonShouldHaveFollowingLinks(string $button, TableNode $dropdownLinks): void
    {
        $links = $this->adminPanelPage->getDropdownOptions($button);

        foreach ($dropdownLinks->getHash() as $link) {
            expect($links)->toContain($link['Link']);
        }
    }

    /**
     * @When /^I click "([^"]*)" link from "([^"]*)" dropdown button$/
     */
    public function iClickLinkFromDropdownButton(string $link, string $dropdown): void
    {
        $this->adminPanelPage->getDropdown($dropdown)->clickLink($link);
    }

    /**
     * @Then /^I should be logged off$/
     */
    public function iShouldBeLoggedOff(): void
    {
        expect($this->kernel->getContainer()->get('security.token_storage')->getToken())->toBe(null);
    }

    /**
     * @Then /^I should see (\d+) error$/
     */
    public function iShouldSeeHttpError(string $httpStatusCode): void
    {
        expect($this->mink->getSession()->getStatusCode())->toBe((int) $httpStatusCode);
    }

    /**
     * @Then /^I should see page header with "([^"]*)" content$/
     */
    public function iShouldSeePageHeaderWithContent(string $headerText): void
    {
        expect($this->getElement('Page Header')->getText())->toBe($headerText);
    }

    /**
     * @Then /^I should see navigation menu with following elements$/
     */
    public function iShouldSeeNavigationMenuWithFollowingElements(TableNode $menu): void
    {
        foreach ($menu->getHash() as $elementData) {
            expect($this->adminPanelPage->hasElementInTopMenu($elementData['Element']))->toBe(true);
        }
    }

    /**
     * @Given /^I should not see "([^"]*)" position in menu$/
     */
    public function iShouldNotSeePositionInMenu(string $elementName): void
    {
        expect($this->adminPanelPage->hasElementInTopMenu($elementName))->toBe(false);
    }

    /**
     * @Given /^I should not see any menu elements$/
     */
    public function iShouldNotSeeAnyElementsInMenu(): void
    {
        expect($this->adminPanelPage->hasAnyMenuElements())->toBe(false);
    }

    public function getPage($name): Page
    {
        switch ($name) {
            case 'Login':
                return $this->loginPage;
            case 'Admin panel':
                return $this->adminPanelPage;
            case 'Admin change password':
                return $this->changePasswordPage;
            case 'User list':
                return $this->userListPage;
            case 'Password reset request':
                return $this->passwordResetRequestPage;
            default:
                throw new InvalidArgumentException(sprintf(
                    'Could not cast "%s" to a page object',
                    $name
                ));
        }
    }
}
