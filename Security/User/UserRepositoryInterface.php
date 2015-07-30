<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Security\User;

interface UserRepositoryInterface
{
    /**
     * @param string $confirmationToken
     * @return UserInterface|null
     */
    public function findUserByConfirmationToken($confirmationToken);

    /**
     * @param string $email
     * @return UserInterface|null
     */
    public function findUserByEmail($email);
}