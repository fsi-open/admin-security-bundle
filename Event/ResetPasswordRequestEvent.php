<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Event;

use FSi\Bundle\AdminSecurityBundle\Security\User\UserPasswordResetInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

class ResetPasswordRequestEvent extends Event
{
    /**
     * @var UserPasswordResetInterface
     */
    private $user;

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserPasswordResetInterface $user
     */
    function __construct(UserPasswordResetInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
