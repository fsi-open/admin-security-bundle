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
use Behat\Mink\Session;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FriendsOfBehat\PageObjectExtension\Page\UnexpectedPageException;
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use FSi\Bundle\AdminSecurityBundle\Behat\Element\FlashMessage;
use FSi\Bundle\AdminSecurityBundle\Behat\Element\PageHeader;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\AdminChangePassword;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\AdminPanel;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\Login;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\Page;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\UserList;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use function sprintf;

final class AdminContext extends AbstractContext
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        Session $session,
        MinkParameters $minkParameters,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($session, $minkParameters, $entityManager);
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Given /^I am on the "([^"]*)" page$/
     */
    public function iAmOnThePage(string $pageName): void
    {
        $this->getPage($pageName)->open();
    }

    /**
     * @Given /^I should be on the "([^"]*)" page$/
     */
    public function iShouldBeOnThePage(string $pageName): void
    {
        /** @var Page $page */
        $page = $this->getPage($pageName);
        Assertion::true($page->isOpen(), "Page \"{$pageName}\" is not open.");
    }

    /**
     * @Given /^I\'m not logged in$/
     */
    public function iAmNotLoggedIn(): void
    {
        if (null !== $this->tokenStorage->getToken()) {
            throw new Exception('User is logged in, though he is not suppose to be!');
        }
    }

    /**
     * @When I impersonate user :user
     */
    public function iImpersonateUser(string $user): void
    {
        $this->getSession()->visit(
            sprintf('%s/admin/?_switch_user=%s', $this->getBaseUrl(), $user)
        );
    }

    /**
     * @Then I should be logged in as :user
     */
    public function iShouldBeLoggedInAs(string $user): void
    {
        $token = $this->tokenStorage->getToken();
        Assertion::notNull($token);
        Assertion::same($token->getUsername(), $user);
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
        Assertion::true(
            $this->getPage($pageName)->isOpen(),
            "Page \"{$pageName}\" is not open."
        );
    }

    /**
     * @Given /^I should see message:$/
     */
    public function iShouldSeeMessage(PyStringNode $message): void
    {
        Assertion::same(
            $this->getElement(FlashMessage::class)->getText(),
            $message->getRaw()
        );
    }

    /**
     * @Then /^I should see dropdown button in navigation bar "([^"]*)"$/
     */
    public function iShouldSeeDropdownButtonInNavigationBar(string $buttonText): void
    {
        Assertion::true(
            $this->getPageObject(AdminPanel::class)->hasLink($buttonText),
            "There is no \"{$buttonText}\" on page."
        );
    }

    /**
     * @Given /^"([^"]*)" dropdown button should have following links$/
     */
    public function dropdownButtonShouldHaveFollowingLinks(string $button, TableNode $dropdownLinks): void
    {
        $links = $this->getPageObject(AdminPanel::class)->getDropdownOptions($button);

        foreach ($dropdownLinks->getHash() as $link) {
            Assertion::contains($links, $link['Link']);
        }
    }

    /**
     * @When /^I click "([^"]*)" link from "([^"]*)" dropdown button$/
     */
    public function iClickLinkFromDropdownButton(string $link, string $dropdown): void
    {
        $this->getPageObject(AdminPanel::class)->getDropdown($dropdown)->clickLink($link);
    }

    /**
     * @Then /^I should be logged off$/
     */
    public function iShouldBeLoggedOff(): void
    {
        Assertion::null(
            $this->tokenStorage->getToken(),
            "User should have been logged out."
        );
    }

    /**
     * @Then /^I should see (\d+) error$/
     */
    public function iShouldSeeHttpError(string $httpStatusCode): void
    {
        Assertion::same(
            $this->getSession()->getStatusCode(),
            (int) $httpStatusCode
        );
    }

    /**
     * @Then /^I should see page header with "([^"]*)" content$/
     */
    public function iShouldSeePageHeaderWithContent(string $headerText): void
    {
        Assertion::same(
            $this->getElement(PageHeader::class)->getText(),
            $headerText
        );
    }

    /**
     * @Then /^I should see navigation menu with following elements$/
     */
    public function iShouldSeeNavigationMenuWithFollowingElements(TableNode $menu): void
    {
        foreach ($menu->getHash() as $elementData) {
            $elementName = $elementData['Element'];
            Assertion::true(
                $this->getPageObject(AdminPanel::class)->hasElementInTopMenu($elementName),
                "No \"{$elementName}\" element in top menu."
            );
        }
    }

    /**
     * @Given /^I should not see "([^"]*)" position in menu$/
     */
    public function iShouldNotSeePositionInMenu(string $elementName): void
    {
        Assertion::false(
            $this->getPageObject(AdminPanel::class)->hasElementInTopMenu($elementName),
            "Element \"{$elementName}\" should not be in top menu."
        );
    }

    /**
     * @Given /^I should not see any menu elements$/
     */
    public function iShouldNotSeeAnyElementsInMenu(): void
    {
        Assertion::false(
            $this->getPageObject(AdminPanel::class)->hasAnyMenuElements(),
            "Top menu should not have any element."
        );
    }

    public function getPage(string $name): Page
    {
        switch ($name) {
            case 'Login':
                $page = $this->getPageObject(Login::class);
                break;
            case 'Admin panel':
                $page = $this->getPageObject(AdminPanel::class);
                break;
            case 'Admin change password':
                $page = $this->getPageObject(AdminChangePassword::class);
                break;
            case 'User list':
                $page = $this->getPageObject(UserList::class);
                break;
            case 'Password reset request':
                $page = $this->getPageObject(AdminChangePassword::class);
                break;
            default:
                throw new InvalidArgumentException(
                    "Could not cast \"{$name}\" to a page object."
                );
        }

        return $page;
    }
}
