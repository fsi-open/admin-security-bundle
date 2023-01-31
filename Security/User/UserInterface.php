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
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

interface UserInterface extends
    ActivableInterface,
    EnforceablePasswordChangeInterface,
    PasswordAuthenticatedUserInterface,
    ResettablePasswordInterface,
    SymfonyUserInterface
{
    public function setUsername(string $username): void;
    public function setEmail(string $email): void;
    public function setLastLogin(DateTimeImmutable $time): void;
    public function getLastLogin(): ?DateTimeImmutable;
    public function addRole(string $role): void;
    public function removeRole(string $role): void;
    // Added until DataGrid issues with accessing empty embeddables is resolved
    public function isActivationTokenNonExpired(): bool;
    public function isPasswordResetTokenNonExpired(): bool;
}
