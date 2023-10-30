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
use function array_values;
use function in_array;
use function strtoupper;

abstract class User implements UserInterface
{
    protected ?int $id;
    protected ?string $username;
    protected ?string $email;
    protected bool $enabled;
    protected bool $enforcePasswordChange;
    /**
     * Encrypted password. Must be persisted.
     */
    protected ?string $password = null;
    /**
     * Plain password. Used for model validation. Must not be persisted.
     */
    protected ?string $plainPassword = null;
    protected ?DateTimeImmutable $lastLogin = null;
    protected ?TokenInterface $activationToken = null;
    protected ?TokenInterface $passwordResetToken = null;
    /**
     * @var array<string>
     */
    protected array $roles;

    public function __construct()
    {
        $this->id = null;
        $this->username = null;
        $this->email = null;
        $this->enabled = false;
        $this->enforcePasswordChange = false;
        $this->password = null;
        $this->plainPassword = null;
        $this->lastLogin = null;
        $this->activationToken = null;
        $this->passwordResetToken = null;
        $this->roles = [];
    }

    /**
     * @return array{
     *   password: string|null,
     *   username: string|null,
     *   enabled: bool,
     *   id: int|null,
     * }
     */
    public function __serialize(): array
    {
        return [
            'password' => $this->password,
            'username' => $this->username,
            'enabled' => $this->enabled,
            'id' => $this->id
        ];
    }

    /**
     * @param array{
     *   password: string|null,
     *   username: string|null,
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
        $this->username = $data['username'];
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->getUserIdentifier();
    }

    public function getUserIdentifier(): string
    {
        return $this->username ?? '';
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

    public function getSalt(): ?string
    {
        return null;
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

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function addRole(string $role): void
    {
        $role = strtoupper($role);
        if (true === in_array($role, $this->roles, true)) {
            return;
        }

        $this->roles[] = $role;
    }

    public function removeRole(string $role): void
    {
        $key = array_search(strtoupper($role), $this->roles, true);
        if (false === $key) {
            return;
        }

        unset($this->roles[$key]);
        $this->roles = array_values($this->roles);
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setEnabled(bool $boolean): void
    {
        $this->enabled = $boolean;
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

    public function isForcedToChangePassword(): bool
    {
        return $this->enforcePasswordChange;
    }

    public function enforcePasswordChange(bool $enforcePasswordChange): void
    {
        $this->enforcePasswordChange = $enforcePasswordChange;
    }
}
