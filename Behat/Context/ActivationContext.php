<?php

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use DateInterval;
use FSi\Bundle\AdminSecurityBundle\Doctrine\UserRepository;
use FSi\Bundle\AdminSecurityBundle\Entity\Token;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ActivationContext extends PageObjectContext implements KernelAwareContext, MinkAwareContext
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
     * @Given /^user "([^"]*)" has activation token "([^"]*)"$/
     */
    public function userHasActivationToken($username, $activationToken)
    {
        $user = $this->getUserRepository()->findOneBy(array('username' => $username));

        $user->setActivationToken($this->createToken($activationToken, new DateInterval('PT3600S')));

        $this->kernel->getContainer()->get('doctrine')->getManagerForClass('FSiFixturesBundle:User')->flush();
    }

    /**
     * @Given /^user "([^"]*)" should still have activation token "([^"]*)"$/
     */
    public function userShouldStillHaveActivationToken($username, $expectedActivationToken)
    {
        $user = $this->getUserRepository()->findOneBy(array('username' => $username));

        expect($user->getActivationToken()->getToken())->toBe($expectedActivationToken);
    }

    /**
     * @Given /^user "([^"]*)" has expired activation token "([^"]*)"$/
     */
    public function userHasActivationTokenWithTtl($username, $activationToken)
    {
        $user = $this->getUserRepository()->findOneBy(array('username' => $username));

        $ttl = new DateInterval('P1D');
        $ttl->invert = true;

        $user->setActivationToken($this->createToken($activationToken, $ttl));

        $this->kernel->getContainer()->get('doctrine')->getManagerForClass('FSiFixturesBundle:User')->flush();
    }

    /**
     * @When /^i try open activation page with token "([^"]*)"$/
     */
    public function iTryOpenActivationPageWithToken($activationToken)
    {
        /** @var \FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\ActivationChangePassword $page */
        $page = $this->getPage('ActivationChangePassword');
        $page->openWithoutVerification(['activationToken' => $activationToken]);
    }

    /**
     * @When /^i open activation page with token "([^"]*)"$/
     */
    public function iOpenActivationPageWithToken($activationToken)
    {
        /** @var \FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\ActivationChangePassword $page */
        $page = $this->getPage('Activation');
        $page->openWithoutVerification(['activationToken' => $activationToken]);
    }

    /**
     * @When /^i open activation page with token received by user "([^"]*)" in the email$/
     */
    public function iOpenActivationPageWithTokenReceivedByUserInTheEmail($userEmail)
    {
        /** @var \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user */
        $user = $this->getUserRepository()->findOneBy(array('email' => $userEmail));

        /** @var \FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\ActivationChangePassword $page */
        $page = $this->getPage('Activation');
        $page->openWithoutVerification(['activationToken' => $user->getActivationToken()->getToken()]);
    }

    /**
     * @Given /^i fill in new password with confirmation$/
     */
    public function iFillInNewPasswordWithConfirmation()
    {
        /** @var \FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\ActivationChangePassword $page */
        $page = $this->getPage('Activation Change Password');
        $page->fillForm();
    }

    /**
     * @Given /^i fill in new password with invalid confirmation$/
     */
    public function iFillInNewPasswordWithInvalidConfirmation()
    {
        /** @var \FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\ActivationChangePassword $page */
        $page = $this->getPage('Activation Change Password');
        $page->fillFormWithInvalidData();
    }

    /**
     * @return UserRepository
     */
    private function getUserRepository()
    {
        return $this->kernel->getContainer()->get('doctrine')->getRepository('FSiFixturesBundle:User');
    }

    /**
     * @param string $token
     * @param \DateInterval $ttl
     * @return \FSi\Bundle\AdminSecurityBundle\Entity\Token
     */
    private function createToken($token, $ttl)
    {
        return new Token(
            $token,
            new \DateTime(),
            $ttl
        );
    }
}
