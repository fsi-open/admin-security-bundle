<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Doctrine;

use Doctrine\ORM\EntityRepository;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findUserByActivationToken($activationToken)
    {
        return $this->findOneBy(['activationToken.token' => $activationToken]);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByPasswordResetToken($confirmationToken)
    {
        return $this->findOneBy(['passwordResetToken.token' => $confirmationToken]);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByEmail($email)
    {
        return $this->findOneBy(['email' => $email]);
    }
}
