<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserRepositoryInterface
{
    /**
     * @param $confirmationToken
     * @return UserInterface|null
     */
    public function findUserByConfirmationToken($confirmationToken);

    /**
     * @param $email
     * @return UserInterface|null
     */
    public function findUserByEmail($email);

    /**
     * @param UserInterface $user
     * @param bool $flush
     * @return
     */
    public function save(UserInterface $user, $flush = true);
}
