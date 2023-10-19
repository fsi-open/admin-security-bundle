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
use DateInterval;
use Exception;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\Activation;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\ActivationChangePassword;
use FSi\Bundle\AdminSecurityBundle\Security\Token\Token;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\FixturesBundle\Entity\User;

final class ActivationContext extends AbstractContext
{
    /**
     * @Given /^user "([^"]*)" has activation token "([^"]*)"$/
     */
    public function userHasActivationToken(string $username, string $activationToken): void
    {
        $user = $this->getUserByUsername($username);
        $user->setActivationToken($this->createToken($activationToken, new DateInterval('PT3600S')));

        $this->getEntityManager()->flush();
    }

    /**
     * @Given /^user "([^"]*)" should still have activation token "([^"]*)"$/
     */
    public function userShouldStillHaveActivationToken(string $username, string $expectedActivationToken): void
    {
        $user = $this->getUserByUsername($username);
        $activationToken = $user->getActivationToken();
        Assertion::notNull($activationToken, "User \"{$username}\" has no activation token.");
        Assertion::same(
            $activationToken->getToken(),
            $expectedActivationToken
        );
    }

    /**
     * @Given /^user "([^"]*)" has expired activation token "([^"]*)"$/
     */
    public function userHasActivationTokenWithTtl(string $username, string $activationToken): void
    {
        $ttl = new DateInterval('P1D');
        $ttl->invert = 1;

        $this->getUserByUsername($username)->setActivationToken($this->createToken($activationToken, $ttl));

        $this->getEntityManager()->flush();
    }

    /**
     * @When /^I try open activation page with token "([^"]*)"$/
     */
    public function iTryOpenActivationPageWithToken(string $activationToken): void
    {
        $this->getPageObject(ActivationChangePassword::class)->openWithoutVerification([
            'activationToken' => $activationToken
        ]);
    }

    /**
     * @When /^I open activation page with token "([^"]*)"$/
     */
    public function iOpenActivationPageWithToken(string $activationToken): void
    {
        $this->getPageObject(Activation::class)->openWithoutVerification(['activationToken' => $activationToken]);
    }

    /**
     * @When /^I open activation page with token received by user "([^"]*)" in the email$/
     */
    public function iOpenActivationPageWithTokenReceivedByUserInTheEmail(string $email): void
    {
        $user = $this->getUserByEmail($email);
        $activationToken = $user->getActivationToken();
        Assertion::notNull($activationToken, "User \"{$email}\" has no activation token.");

        $this->getPageObject(Activation::class)->openWithoutVerification([
            'activationToken' => $activationToken->getToken()
        ]);
    }

    /**
     * @Given /^I fill in new password with confirmation$/
     */
    public function iFillInNewPasswordWithConfirmation(): void
    {
        $this->getPageObject(ActivationChangePassword::class)->fillForm();
    }

    /**
     * @Given /^I fill in new password with invalid confirmation$/
     */
    public function iFillInNewPasswordWithInvalidConfirmation(): void
    {
        $this->getPageObject(ActivationChangePassword::class)->fillFormWithInvalidData();
    }

    public function getUserByEmail(string $email): UserInterface
    {
        $user = $this->getRepository(User::class)->findOneBy(['email' => $email]);
        if (null === $user) {
            throw new Exception("No user for email \"{$email}\"");
        }

        return $user;
    }

    public function getUserByUsername(string $username): UserInterface
    {
        $user = $this->getRepository(User::class)->findOneBy(['username' => $username]);
        if (null === $user) {
            throw new Exception("No user for username \"{$username}\"");
        }

        return $user;
    }

    private function createToken(string $token, DateInterval $ttl): Token
    {
        return new Token($token, $ttl);
    }
}
