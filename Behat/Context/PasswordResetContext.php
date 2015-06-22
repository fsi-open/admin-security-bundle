<?php

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use DateInterval;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
        $userManager = $this->getUserManager();

        $user = $userManager->findUserByUsername($username);

        $user->setConfirmationToken($confirmationToken);
        $user->setPasswordRequestedAt(new \DateTime());

        $userManager->updateUser($user);
    }

    /**
     * @Given /^user "([^"]*)" should still have confirmation token "([^"]*)"$/
     */
    public function userShouldStillHaveConfirmationToken($username, $expectedConfirmationToken)
    {
        $userManager = $this->getUserManager();

        $user = $userManager->findUserByUsername($username);

        expect($user->getConfirmationToken())->toBe($expectedConfirmationToken);
    }

    /**
     * @Given /^user "([^"]*)" has expired confirmation token "([^"]*)"$/
     */
    public function userHasConfirmationTokenWithTtl($username, $confirmationToken)
    {
        $userManager = $this->getUserManager();

        $user = $userManager->findUserByUsername($username);

        $passwordRequestedAt = new \DateTime();
        $passwordRequestedAt->sub(new DateInterval('P1D'));

        $user->setConfirmationToken($confirmationToken);
        $user->setPasswordRequestedAt($passwordRequestedAt);

        $userManager->updateUser($user);
    }

    /**
     * @When /^I fill form with non-existent email address$/
     */
    public function iFillFormWithNonExistentEmailAddress()
    {
        $this->getPage('Password Reset Request')->fillField('Email', 'nonexistent@fsi.pl');
    }

    /**
     * @Given /^I should be on the "([^"]*)" page$/
     */
    public function iShouldBeOnThePage($pageName)
    {
        /** @var \SensioLabs\Behat\PageObjectExtension\PageObject\Page $page */
        $page = $this->getPage($pageName);
        $page->isOpen();
    }

    /**
     * @When /^I fill form with correct email address$/
     */
    public function iFillFormWithCorrectEmailAddress()
    {
        $this->getPage('Password Reset Request')->fillField('Email', 'admin@fsi.pl');
    }

    /**
     * @Then /^I should see password reset request form message "([^"]*)"$/
     */
    public function iShouldSeePasswordResetRequestFormMessage($message)
    {
        /** @var \FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\PasswordResetRequest $page */
        $page = $this->getPage('Password Reset Request');
        expect($page->getMessage())->toBe($message);
    }

    /**
     * @When /^i try open password change page with token "([^"]*)"$/
     */
    public function iTryOpenPasswordChangePageWithToken($confirmationToken)
    {
        /** @var \FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\PasswordResetChangePassword $page */
        $page = $this->getPage('Password Reset Change Password');
        $page->openWithoutVerification(['confirmationToken' => $confirmationToken]);
    }

    /**
     * @When /^i open password change page with token "([^"]*)"$/
     */
    public function iOpenPasswordChangePageWithToken($confirmationToken)
    {
        /** @var \FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\PasswordResetChangePassword $page */
        $page = $this->getPage('Password Reset Change Password');
        $page->open(['confirmationToken' => $confirmationToken]);
    }

    /**
     * @Then /^i should see (\d+) error$/
     */
    public function iShouldSeeHttpError($httpStatusCode)
    {
        expect($this->getMink()->getSession()->getStatusCode())->toBe(intval($httpStatusCode));
    }

    /**
     * @Given /^i fill in new password with confirmation$/
     */
    public function iFillInNewPasswordWithConfirmation()
    {
        /** @var \FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\PasswordResetChangePassword $page */
        $page = $this->getPage('Password Reset Change Password');
        $page->fillForm();
    }

    /**
     * @Given /^i fill in new password with invalid confirmation$/
     */
    public function iFillInNewPasswordWithInvalidConfirmation()
    {
        /** @var \FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\PasswordResetChangePassword $page */
        $page = $this->getPage('Password Reset Change Password');
        $page->fillFormWithInvalidData();
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

    /**
     * @Then /^user "([^"]*)" should have changed password$/
     */
    public function userShouldHaveChangedPassword($userEmail)
    {
        $user = $this->getUserManager()->findUserByEmail($userEmail);

        expect($user->getPassword())->toBe($this->encodePassword($user, 'test'));
    }

    /**
     * @param UserInterface $user
     * @param $password
     * @return string
     */
    private function encodePassword(UserInterface $user, $password)
    {
        /** @var \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $encoder */
        $encoder = $this->kernel->getContainer()->get('security.password_encoder');

        return $encoder->encodePassword($user, $password);
    }

    /**
     * @return \FOS\UserBundle\Doctrine\UserManager
     */
    private function getUserManager()
    {
        return $this->kernel->getContainer()->get('fos_user.user_manager');
    }
}
