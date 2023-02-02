<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\spec\fixtures;

use DateTimeImmutable;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;

class User implements UserInterface
{
    private ?string $password = null;

    public function __toString()
    {
        return '';
    }

    public function getRoles(): array
    {
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getUsername(): ?string
    {
    }

    public function eraseCredentials(): void
    {
    }

    public function setEnabled(bool $boolean): void
    {
    }

    public function getActivationToken(): ?TokenInterface
    {
    }

    public function setActivationToken(TokenInterface $token): void
    {
    }

    public function removeActivationToken(): void
    {
    }

    public function isEnabled(): bool
    {
    }

    public function getPlainPassword(): ?string
    {
    }

    public function setPlainPassword(string $password): void
    {
    }

    public function getEmail(): ?string
    {
    }

    public function isForcedToChangePassword(): bool
    {
    }

    public function enforcePasswordChange(bool $enforce): void
    {
    }

    public function getPasswordResetToken(): ?TokenInterface
    {
    }

    public function setPasswordResetToken(TokenInterface $token): void
    {
    }

    public function removePasswordResetToken(): void
    {
    }

    public function setUsername(string $username): void
    {
    }

    public function setEmail(string $email): void
    {
    }

    public function setLocked(bool $boolean): void
    {
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function setLastLogin(DateTimeImmutable $time): void
    {
    }

    public function getLastLogin(): ?DateTimeImmutable
    {
    }

    public function addRole(string $role): void
    {
    }

    public function removeRole(string $role): void
    {
    }
}
