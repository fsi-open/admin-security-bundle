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
use DateTimeImmutable;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\Page;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\PasswordResetChangePassword;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\PasswordResetRequest;
use FSi\Bundle\AdminSecurityBundle\Security\Token\Token;
use FSi\FixturesBundle\Entity\User;

final class PasswordResetContext extends AbstractContext
{
    /**
     * @Given /^user "([^"]*)" has confirmation token "([^"]*)"$/
     */
    public function userHasConfirmationToken(string $username, string $confirmationToken): void
    {
        $user = $this->findUserByUsername($username);
        $user->setPasswordResetToken($this->createToken($confirmationToken, new DateInterval('PT3600S')));

        $this->getEntityManager()->flush();
    }

    /**
     * @Given /^user "([^"]*)" should still have confirmation token "([^"]*)"$/
     */
    public function userShouldStillHaveConfirmationToken(string $username, string $expectedConfirmationToken): void
    {
        $user = $this->findUserByUsername($username);
        Assertion::same(
            $user->getPasswordResetToken()->getToken(),
            $expectedConfirmationToken
        );
    }

    /**
     * @Given /^user "([^"]*)" has expired confirmation token "([^"]*)"$/
     */
    public function userHasConfirmationTokenWithTtl(string $username, string $confirmationToken): void
    {
        $user = $this->findUserByUsername($username);
        $ttl = new DateInterval('P1D');
        $ttl->invert = 1;

        $user->setPasswordResetToken($this->createToken($confirmationToken, $ttl));

        $this->getEntityManager()->flush();
    }

    /**
     * @When /^I fill form with non-existent email address$/
     */
    public function iFillFormWithNonExistentEmailAddress(): void
    {
        $this->getPasswordResetRequestPage()->fillField('Email', 'nonexistent@fsi.pl');
    }

    /**
     * @When /^I fill form with correct email address$/
     */
    public function iFillFormWithCorrectEmailAddress(): void
    {
        $this->getPasswordResetRequestPage()->fillField('Email', 'admin@fsi.pl');
    }

    /**
     * @When /^I try open password change page with token "([^"]*)"$/
     */
    public function iTryOpenPasswordChangePageWithToken(string $confirmationToken): void
    {
        $this->getPasswordResetChangePasswordPage()->openWithoutVerification(['confirmationToken' => $confirmationToken]);
    }

    /**
     * @When /^I open password change page with token "([^"]*)"$/
     */
    public function iOpenPasswordChangePageWithToken(string $confirmationToken): void
    {
        $this->getPasswordResetChangePasswordPage()->open(['confirmationToken' => $confirmationToken]);
    }

    /**
     * @Given /^I fill in new password with confirmation$/
     */
    public function iFillInNewPasswordWithConfirmation(): void
    {
        $this->getPasswordResetChangePasswordPage()->fillForm();
    }

    /**
     * @Given /^I fill in new password with invalid confirmation$/
     */
    public function iFillInNewPasswordWithInvalidConfirmation(): void
    {
        $this->getPasswordResetChangePasswordPage()->fillFormWithInvalidData();
    }

    private function findUserByUsername(string $username): User
    {
        $user = $this->getRepository(User::class)->findOneBy(['username' => $username]);
        Assertion::notNull($user, "No user for username \"{$username}\".");

        return $user;
    }

    private function createToken(string $confirmationToken, DateInterval $ttl): Token
    {
        return new Token($confirmationToken, new DateTimeImmutable(), $ttl);
    }

    private function getPasswordResetRequestPage(): PasswordResetRequest
    {
        return $this->getPageObject(PasswordResetRequest::class);
    }

    private function getPasswordResetChangePasswordPage(): PasswordResetChangePassword
    {
        return $this->getPageObject(PasswordResetChangePassword::class);
    }
}
