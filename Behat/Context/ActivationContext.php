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
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\Activation;
use FSi\Bundle\AdminSecurityBundle\Behat\Context\Page\ActivationChangePassword;
use FSi\Bundle\AdminSecurityBundle\Security\Token\Token;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use FSi\FixturesBundle\Entity\User;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Symfony\Component\HttpKernel\KernelInterface;

use function expect;

final class ActivationContext extends PageObjectContext implements KernelAwareContext, MinkAwareContext
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
    public function userHasActivationToken(string $username, string $activationToken): void
    {
        $user = $this->getUserByUsername($username);
        $user->setActivationToken($this->createToken($activationToken, new DateInterval('PT3600S')));

        $this->flush();
    }

    /**
     * @Given /^user "([^"]*)" should still have activation token "([^"]*)"$/
     */
    public function userShouldStillHaveActivationToken(string $username, $expectedActivationToken): void
    {
        $user = $this->getUserByUsername($username);

        expect($user->getActivationToken()->getToken())->toBe($expectedActivationToken);
    }

    /**
     * @Given /^user "([^"]*)" has expired activation token "([^"]*)"$/
     */
    public function userHasActivationTokenWithTtl(string $username, string $activationToken): void
    {
        $ttl = new DateInterval('P1D');
        $ttl->invert = true;

        $this->getUserByUsername($username)->setActivationToken($this->createToken($activationToken, $ttl));

        $this->flush();
    }

    /**
     * @When /^I try open activation page with token "([^"]*)"$/
     */
    public function iTryOpenActivationPageWithToken(string $activationToken): void
    {
        $this->activationChangePasswordPage->openWithoutVerification(['activationToken' => $activationToken]);
    }

    /**
     * @When /^I open activation page with token "([^"]*)"$/
     */
    public function iOpenActivationPageWithToken(string $activationToken): void
    {
        $this->activationPage->openWithoutVerification(['activationToken' => $activationToken]);
    }

    /**
     * @When /^I open activation page with token received by user "([^"]*)" in the email$/
     */
    public function iOpenActivationPageWithTokenReceivedByUserInTheEmail(string $email): void
    {
        $user = $this->getUserByEmail($email);
        $this->activationPage->openWithoutVerification([
            'activationToken' => $user->getActivationToken()->getToken()
        ]);
    }

    /**
     * @Given /^I fill in new password with confirmation$/
     */
    public function iFillInNewPasswordWithConfirmation(): void
    {
        $this->activationChangePasswordPage->fillForm();
    }

    /**
     * @Given /^I fill in new password with invalid confirmation$/
     */
    public function iFillInNewPasswordWithInvalidConfirmation(): void
    {
        $this->activationChangePasswordPage->fillFormWithInvalidData();
    }

    public function getUserByEmail(string $email): UserInterface
    {
        $user = $this->getUserRepository()->findOneBy(['email' => $email]);
        if (null === $user) {
            throw new Exception("No user for email \"{$email}\"");
        }

        return $user;
    }

    public function getUserByUsername(string $username): UserInterface
    {
        $user = $this->getUserRepository()->findOneBy(['username' => $username]);
        if (null === $user) {
            throw new Exception("No user for username \"{$username}\"");
        }

        return $user;
    }

    private function flush(): void
    {
        $manager = $this->getDoctrine()->getManagerForClass(User::class);
        if (null === $manager) {
            throw new Exception(sprintf('Unable to fetch manager for class "%s"', User::class));
        }

        $manager->flush();
    }

    private function getDoctrine(): ManagerRegistry
    {
        $doctrine = $this->kernel->getContainer()->get('doctrine');
        if (false === $doctrine instanceof ManagerRegistry) {
            throw new Exception('Unable to fetch Doctrine');
        }

        return $doctrine;
    }

    private function getUserRepository(): UserRepositoryInterface
    {
        return $this->getDoctrine()->getRepository(User::class);
    }

    /**
     * @param string $token
     * @param DateInterval $ttl
     * @return Token
     */
    private function createToken(string $token, DateInterval $ttl): Token
    {
        return new Token($token, new DateTime(), $ttl);
    }
}
