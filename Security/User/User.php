<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Security\User;

use DateTimeImmutable;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;

use function array_fill;
use function array_merge;
use function array_search;
use function array_unique;
use function base_convert;
use function in_array;
use function mt_rand;
use function sha1;
use function strtoupper;
use function time;
use function uniqid;

abstract class User implements UserInterface
{
    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var string|null
     */
    protected $username;

    /**
     * @var string|null
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
     * @var string|null
     */
    protected $salt;

    /**
     * Encrypted password. Must be persisted.
     *
     * @var string|null
     */
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string|null
     */
    protected $plainPassword;

    /**
     * @var DateTimeImmutable|null
     */
    protected $lastLogin;

    /**
     * @var TokenInterface|null
     */
    protected $activationToken;

    /**
     * @var TokenInterface|null
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
     * @var DateTimeImmutable|null
     */
    protected $expiresAt;

    /**
     * @var array<string>
     */
    protected $roles;

    /**
     * @var boolean
     */
    protected $credentialsExpired;

    /**
     * @var DateTimeImmutable|null
     */
    protected $credentialsExpireAt;

    public function __construct()
    {
        $this->salt = base_convert(sha1(uniqid((string) mt_rand(), true)), 16, 36);
        $this->enabled = false;
        $this->locked = false;
        $this->enforcePasswordChange = false;
        $this->expired = false;
        $this->roles = [];
        $this->credentialsExpired = false;
    }

    /**
     * @return array{
     *   password: string|null,
     *   salt: string|null,
     *   username: string|null,
     *   expired: bool,
     *   locked: bool,
     *   credentialsExpired: bool,
     *   enabled: bool,
     *   id: int|null,
     * }
     */
    public function __serialize(): array
    {
        return [
            'password' => $this->password,
            'salt' => $this->salt,
            'username' => $this->username,
            'expired' => $this->expired,
            'locked' => $this->locked,
            'credentialsExpired' => $this->credentialsExpired,
            'enabled' => $this->enabled,
            'id' => $this->id
        ];
    }

    /**
     * @param array{
     *   password: string|null,
     *   salt: string|null,
     *   username: string|null,
     *   expired: bool,
     *   locked: bool,
     *   credentialsExpired: bool,
     *   enabled: bool,
     *   id: int|null,
     * } $serialized
     */
    public function __unserialize($serialized): void
    {
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($serialized, array_fill(0, 2, null));

        $this->password = $data['password'];
        $this->salt = $data['salt'];
        $this->username = $data['username'];
        $this->expired = $data['expired'];
        $this->locked = $data['locked'];
        $this->credentialsExpired = $data['credentialsExpired'];
        $this->enabled = $data['enabled'];
        $this->id = $data['id'];
    }

    public function __toString(): string
    {
        return (string) $this->getUsername();
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return int|null
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

    public function getLastLogin(): ?DateTimeImmutable
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
        return false === $this->locked;
    }

    public function isCredentialsNonExpired(): bool
    {
        if (true === $this->credentialsExpired) {
            return false;
        }

        if (
            null !== $this->credentialsExpireAt
            && $this->credentialsExpireAt->getTimestamp() < time()
        ) {
            return false;
        }

        return true;
    }

    public function isCredentialsExpired(): bool
    {
        return false === $this->isCredentialsNonExpired();
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isExpired(): bool
    {
        return false === $this->isAccountNonExpired();
    }

    public function isLocked(): bool
    {
        return false === $this->isAccountNonLocked();
    }

    public function addRole(string $role): void
    {
        $role = strtoupper($role);
        if (false === in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
    }

    public function removeRole(string $role): void
    {
        $key = array_search(strtoupper($role), $this->roles, true);
        if (false !== $key) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setCredentialsExpireAt(DateTimeImmutable $date): void
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

    public function setExpiresAt(DateTimeImmutable $date): void
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

    public function setLastLogin(DateTimeImmutable $time): void
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
}
