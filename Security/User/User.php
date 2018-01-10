<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Security\User;

use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;

abstract class User implements UserInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * @var boolean
     */
    protected $enforcePasswordChange;

    /**
     * The salt to use for hashing
     *
     * @var string
     */
    protected $salt;

    /**
     * Encrypted password. Must be persisted.
     *
     * @var string
     */
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     */
    protected $plainPassword;

    /**
     * @var \DateTime
     */
    protected $lastLogin;

    /**
     * @var TokenInterface
     */
    protected $activationToken;

    /**
     * @var TokenInterface
     */
    protected $passwordResetToken;

    /**
     * @var boolean
     */
    protected $locked;

    /**
     * @var boolean
     */
    protected $expired;

    /**
     * @var \DateTime
     */
    protected $expiresAt;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var boolean
     */
    protected $credentialsExpired;

    /**
     * @var \DateTime
     */
    protected $credentialsExpireAt;

    public function __construct()
    {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->enabled = false;
        $this->locked = false;
        $this->enforcePasswordChange = false;
        $this->expired = false;
        $this->roles = [];
        $this->credentialsExpired = false;
    }

    /**
     * Serializes the user.
     *
     * The serialized data have to contain the fields used by the equals method and the username.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->password,
            $this->salt,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id
        ]);
    }

    /**
     * Unserializes the user.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 2, null));

        list(
            $this->password,
            $this->salt,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id
            ) = $data;
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * Returns the user unique id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function getActivationToken(): ?TokenInterface
    {
        return $this->activationToken;
    }

    public function setActivationToken(TokenInterface $activationToken): void
    {
        $this->activationToken = $activationToken;
    }

    public function removeActivationToken(): void
    {
        $this->activationToken = null;
    }

    public function getPasswordResetToken(): ?TokenInterface
    {
        return $this->passwordResetToken;
    }

    public function setPasswordResetToken(TokenInterface $passwordResetToken): void
    {
        $this->passwordResetToken = $passwordResetToken;
    }

    public function removePasswordResetToken(): void
    {
        $this->passwordResetToken = null;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function isAccountNonExpired(): bool
    {
        if (true === $this->expired) {
            return false;
        }

        if (null !== $this->expiresAt && $this->expiresAt->getTimestamp() < time()) {
            return false;
        }

        return true;
    }

    public function isAccountNonLocked(): bool
    {
        return !$this->locked;
    }

    public function isCredentialsNonExpired(): bool
    {
        if (true === $this->credentialsExpired) {
            return false;
        }

        if (null !== $this->credentialsExpireAt && $this->credentialsExpireAt->getTimestamp() < time()) {
            return false;
        }

        return true;
    }

    public function isCredentialsExpired(): bool
    {
        return !$this->isCredentialsNonExpired();
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isExpired(): bool
    {
        return !$this->isAccountNonExpired();
    }

    public function isLocked(): bool
    {
        return !$this->isAccountNonLocked();
    }

    public function addRole(string $role): void
    {
        $role = strtoupper($role);

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
    }

    public function removeRole(string $role): void
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setCredentialsExpireAt(\DateTime $date): void
    {
        $this->credentialsExpireAt = $date;
    }

    public function setCredentialsExpired(bool $boolean): void
    {
        $this->credentialsExpired = $boolean;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setEnabled(bool $boolean): void
    {
        $this->enabled = $boolean;
    }

    public function setExpired(bool $boolean): void
    {
        $this->expired = $boolean;
    }

    public function setExpiresAt(\DateTime $date): void
    {
        $this->expiresAt = $date;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setPlainPassword(string $password): void
    {
        $this->plainPassword = $password;
    }

    public function setLastLogin(\DateTime $time): void
    {
        $this->lastLogin = $time;
    }

    public function setLocked(bool $boolean): void
    {
        $this->locked = $boolean;
    }

    public function isForcedToChangePassword(): bool
    {
        return $this->enforcePasswordChange;
    }

    public function enforcePasswordChange(bool $enforcePasswordChange): void
    {
        $this->enforcePasswordChange = $enforcePasswordChange;
    }

    public function __toString(): string
    {
        return (string) $this->getUsername();
    }
}
