<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    public function findUserByActivationToken(string $activationToken): ?ActivableInterface
    {
        return $this->findOneBy(['activationToken.token' => $activationToken]);
    }

    public function findUserByPasswordResetToken(string $confirmationToken): ?ResettablePasswordInterface
    {
        return $this->findOneBy(['passwordResetToken.token' => $confirmationToken]);
    }

    public function findUserByEmail(string $email): ?SymfonyUserInterface
    {
        return $this->findOneBy(['email' => $email]);
    }
}
