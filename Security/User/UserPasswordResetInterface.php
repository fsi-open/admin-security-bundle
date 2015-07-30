<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Security\User;

use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;

interface UserPasswordResetInterface extends UserPasswordChangeInterface
{
    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return TokenInterface
     */
    public function getPasswordResetToken();

    /**
     * @param TokenInterface $token
     */
    public function setPasswordResetToken(TokenInterface $token);

    public function removePasswordResetToken();
}
