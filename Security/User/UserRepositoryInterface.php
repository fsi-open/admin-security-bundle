<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

interface UserRepositoryInterface
{
    public function findUserByPasswordResetToken(string $confirmationToken): ?ResettablePasswordInterface;

    public function findUserByActivationToken(string $activationToken): ?ActivableInterface;

    public function findUserByEmail(string $email): ?SymfonyUserInterface;
}
