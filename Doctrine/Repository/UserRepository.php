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
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use RuntimeException;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

use function get_class;

/**
 * @extends EntityRepository<SymfonyUserInterface>
 */
class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    public function findUserByActivationToken(string $activationToken): ?ActivableInterface
    {
        $user = $this->findOneBy(['activationToken.token' => $activationToken]);
        if (null === $user) {
            return null;
        }

        if (false === $user instanceof ActivableInterface || false === $user instanceof ChangeablePasswordInterface) {
            throw new RuntimeException(
                sprintf(
                    'Class "%s" does not implement both "%s" and "%s"',
                    get_class($user),
                    ActivableInterface::class,
                    ChangeablePasswordInterface::class
                )
            );
        }

        return $user;
    }

    public function findUserByPasswordResetToken(string $confirmationToken): ?ResettablePasswordInterface
    {
        $user = $this->findOneBy(['passwordResetToken.token' => $confirmationToken]);
        if (null !== $user && false === $user instanceof ResettablePasswordInterface) {
            throw new RuntimeException(sprintf(
                'Entity of class "%s" does not implement the "%s" interface.',
                get_class($user),
                ResettablePasswordInterface::class
            ));
        }

        return $user;
    }

    public function findUserByEmail(string $email): ?SymfonyUserInterface
    {
        return $this->findOneBy(['email' => $email]);
    }
}
