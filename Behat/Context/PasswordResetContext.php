<?php

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use DateInterval;
use DateTime;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\PasswordResetChangePassword;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\PasswordResetRequest;
use FSi\Bundle\AdminSecurityBundle\Doctrine\Repository\UserRepository;
use FSi\Bundle\AdminSecurityBundle\Security\Token\Token;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Symfony\Component\HttpKernel\KernelInterface;

class PasswordResetContext extends PageObjectContext implements KernelAwareContext, MinkAwareContext
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
     * @var PasswordResetRequest
     */
    private $passwordResetRequestPage;

    /**
     * @var PasswordResetChangePassword
     */
    private $passwordResetChangePage;

    public function __construct(
        PasswordResetRequest $passwordResetRequestPage,
        PasswordResetChangePassword $passwordResetChangePage
    ) {
        $this->passwordResetRequestPage = $passwordResetRequestPage;
        $this->passwordResetChangePage = $passwordResetChangePage;
    }

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
     * @param string|null $name name of the session OR active session will be used
     * @return Session
     */
    public function getSession($name = null)
    {
        return $this->getMink()->getSession($name);
    }

    /**
     * @Given /^user "([^"]*)" has confirmation token "([^"]*)"$/
     */
    public function userHasConfirmationToken($username, $confirmationToken)
    {
        $user = $this->getUserRepository()->findOneBy(['username' => $username]);

        $user->setPasswordResetToken($this->createToken($confirmationToken, new DateInterval('PT3600S')));

        $this->kernel->getContainer()->get('doctrine')->getManagerForClass('FSiFixturesBundle:User')->flush();
    }

    /**
     * @Given /^user "([^"]*)" should still have confirmation token "([^"]*)"$/
     */
    public function userShouldStillHaveConfirmationToken($username, $expectedConfirmationToken)
    {
        $user = $this->getUserRepository()->findOneBy(['username' => $username]);

        expect($user->getPasswordResetToken()->getToken())->toBe($expectedConfirmationToken);
    }

    /**
     * @Given /^user "([^"]*)" has expired confirmation token "([^"]*)"$/
     */
    public function userHasConfirmationTokenWithTtl($username, $confirmationToken)
    {
        $user = $this->getUserRepository()->findOneBy(['username' => $username]);

        $ttl = new DateInterval('P1D');
        $ttl->invert = true;

        $user->setPasswordResetToken($this->createToken($confirmationToken, $ttl));

        $this->kernel->getContainer()->get('doctrine')->getManagerForClass('FSiFixturesBundle:User')->flush();
    }

    /**
     * @When /^I fill form with non-existent email address$/
     */
    public function iFillFormWithNonExistentEmailAddress()
    {
        $this->passwordResetRequestPage->fillField('Email', 'nonexistent@fsi.pl');
    }

    /**
     * @Given /^I should be on the "([^"]*)" page$/
     */
    public function iShouldBeOnThePage($pageName)
    {
        /** @var Page $page */
        $page = $this->getPage($pageName);
        expect($page->isOpen())->toBe(true);
    }

    /**
     * @When /^I fill form with correct email address$/
     */
    public function iFillFormWithCorrectEmailAddress()
    {
        $this->passwordResetRequestPage->fillField('Email', 'admin@fsi.pl');
    }

    /**
     * @When /^i try open password change page with token "([^"]*)"$/
     */
    public function iTryOpenPasswordChangePageWithToken($confirmationToken)
    {
        $this->passwordResetChangePage->openWithoutVerification(['confirmationToken' => $confirmationToken]);
    }

    /**
     * @When /^i open password change page with token "([^"]*)"$/
     */
    public function iOpenPasswordChangePageWithToken($confirmationToken)
    {
        $this->passwordResetChangePage->open(['confirmationToken' => $confirmationToken]);
    }

    /**
     * @Given /^i fill in new password with confirmation$/
     */
    public function iFillInNewPasswordWithConfirmation()
    {
        $this->passwordResetChangePage->fillForm();
    }

    /**
     * @Given /^i fill in new password with invalid confirmation$/
     */
    public function iFillInNewPasswordWithInvalidConfirmation()
    {
        $this->passwordResetChangePage->fillFormWithInvalidData();
    }

    /**
     * @return UserRepository
     */
    private function getUserRepository()
    {
        return $this->kernel->getContainer()->get('doctrine')->getRepository('FSiFixturesBundle:User');
    }

    /**
     * @param string $confirmationToken
     * @param \DateInterval $ttl
     * @return Token
     */
    private function createToken($confirmationToken, DateInterval $ttl)
    {
        return new Token($confirmationToken, new DateTime(), $ttl);
    }
}
