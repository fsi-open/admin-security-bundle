<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\spec\fixtures;

use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;

class FSiUserRepository implements UserRepositoryInterface
{
    public function findUserByPasswordResetToken($confirmationToken)
    {
    }

    public function findUserByActivationToken($activationToken)
    {
    }

    public function findUserByEmail($email)
    {
    }
}
