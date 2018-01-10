<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\spec\fixtures;

use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;

class User implements UserInterface
{
    private $password;

    public function getRoles()
    {
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
    }

    public function eraseCredentials()
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

    public function isAccountNonExpired()
    {
    }

    public function isAccountNonLocked()
    {
    }

    public function isCredentialsNonExpired()
    {
    }

    public function isEnabled()
    {
    }

    public function getPlainPassword(): ?string
    {
    }

    public function setPlainPassword(string $password): void
    {
    }

    public function serialize()
    {
    }

    public function unserialize($serialized)
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

    public function setLastLogin(\DateTime $time): void
    {
    }

    public function getLastLogin(): ?\DateTime
    {
    }

    public function addRole(string $role): void
    {
    }

    public function removeRole(string $role): void
    {
    }
}
