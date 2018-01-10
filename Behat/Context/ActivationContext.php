<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Mink\Mink;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use DateInterval;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\Activation;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\ActivationChangePassword;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\Token\Token;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Symfony\Component\HttpKernel\KernelInterface;

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
     * @var Activation
     */
    private $activationPage;

    /**
     * @var ActivationChangePassword
     */
    private $activationChangePasswordPage;

    public function __construct(Activation $activationPage, ActivationChangePassword $activationChangePasswordPage)
    {
        $this->activationPage = $activationPage;
        $this->activationChangePasswordPage = $activationChangePasswordPage;
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

    public function getSession(?string $name = null): Session
    {
        return $this->getMink()->getSession($name);
    }

    /**
     * @Given /^user "([^"]*)" has activation token "([^"]*)"$/
     */
    public function userHasActivationToken($username, $activationToken)
    {
        $user = $this->getUserRepository()->findOneBy(['username' => $username]);

        $user->setActivationToken($this->createToken($activationToken, new DateInterval('PT3600S')));

        $this->kernel->getContainer()->get('doctrine')->getManagerForClass('FSiFixturesBundle:User')->flush();
    }

    /**
     * @Given /^user "([^"]*)" should still have activation token "([^"]*)"$/
     */
    public function userShouldStillHaveActivationToken($username, $expectedActivationToken)
    {
        $user = $this->getUserRepository()->findOneBy(['username' => $username]);

        expect($user->getActivationToken()->getToken())->toBe($expectedActivationToken);
    }

    /**
     * @Given /^user "([^"]*)" has expired activation token "([^"]*)"$/
     */
    public function userHasActivationTokenWithTtl($username, $activationToken)
    {
        $user = $this->getUserRepository()->findOneBy(['username' => $username]);

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
        $this->activationChangePasswordPage->openWithoutVerification(['activationToken' => $activationToken]);
    }

    /**
     * @When /^i open activation page with token "([^"]*)"$/
     */
    public function iOpenActivationPageWithToken($activationToken)
    {
        $this->activationPage->openWithoutVerification(['activationToken' => $activationToken]);
    }

    /**
     * @When /^i open activation page with token received by user "([^"]*)" in the email$/
     */
    public function iOpenActivationPageWithTokenReceivedByUserInTheEmail($userEmail)
    {
        /** @var UserInterface $user */
        $user = $this->getUserRepository()->findOneBy(['email' => $userEmail]);
        $this->activationPage->openWithoutVerification(['activationToken' => $user->getActivationToken()->getToken()]);
    }

    /**
     * @Given /^i fill in new password with confirmation$/
     */
    public function iFillInNewPasswordWithConfirmation()
    {
        $this->activationChangePasswordPage->fillForm();
    }

    /**
     * @Given /^i fill in new password with invalid confirmation$/
     */
    public function iFillInNewPasswordWithInvalidConfirmation()
    {
        $this->activationChangePasswordPage->fillFormWithInvalidData();
    }

    private function getUserRepository(): UserRepositoryInterface
    {
        return $this->kernel->getContainer()->get('doctrine')->getRepository('FSiFixturesBundle:User');
    }

    /**
     * @param string $token
     * @param \DateInterval $ttl
     * @return Token
     */
    private function createToken($token, $ttl): Token
    {
        return new Token($token, new \DateTime(), $ttl);
    }
}
