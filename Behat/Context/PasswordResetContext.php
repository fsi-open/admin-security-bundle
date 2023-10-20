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
use Behat\Mink\Session;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\PasswordResetChangePassword;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\PasswordResetRequest;
use FSi\Bundle\AdminSecurityBundle\Security\Token\Token;
use FSi\FixturesBundle\Entity\User;
use FSi\FixturesBundle\Time\Clock;

final class PasswordResetContext extends AbstractContext
{
    private Clock $clock;

    public function __construct(
        Session $session,
        MinkParameters $minkParameters,
        EntityManagerInterface $entityManager,
        Clock $clock
    ) {
        parent::__construct($session, $minkParameters, $entityManager);
        $this->clock = $clock;
    }

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
        $passwordResetToken = $user->getPasswordResetToken();
        Assertion::notNull(
            $passwordResetToken,
            "User {$username} has no password reset token"
        );
        Assertion::same(
            $passwordResetToken->getToken(),
            $expectedConfirmationToken
        );
    }

    /**
     * @Given /^user "([^"]*)" has expired confirmation token "([^"]*)"$/
     */
    public function userHasConfirmationTokenWithTtl(string $username, string $confirmationToken): void
    {
        $this->clock->freeze((new DateTimeImmutable())->sub(new DateInterval('P2D')));

        $user = $this->findUserByUsername($username);

        $user->setPasswordResetToken($this->createToken($confirmationToken, new DateInterval('P1D')));

        $this->getEntityManager()->flush();

        $this->clock->return();
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
        $this->getPasswordResetChangePasswordPage()->openWithoutVerification([
            'confirmationToken' => $confirmationToken
        ]);
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
        return new Token($confirmationToken, $this->clock, $ttl);
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
