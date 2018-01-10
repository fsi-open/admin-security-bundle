<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Security\User;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

interface UserInterface extends
    AdvancedUserInterface,
    ResettablePasswordInterface,
    EnforceablePasswordChangeInterface,
    ActivableInterface,
    \Serializable
{
    public function setUsername(string $username): void;

    public function setEmail(string $email): void;

    public function setLocked(bool $boolean): void;

    public function setLastLogin(\DateTime $time): void;

    public function getLastLogin(): ?\DateTime;

    public function addRole(string $role): void;

    public function removeRole(string $role): void;
}
