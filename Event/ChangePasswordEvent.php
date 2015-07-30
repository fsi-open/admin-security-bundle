<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Event;

use FSi\Bundle\AdminSecurityBundle\Security\User\UserPasswordChangeInterface;
use Symfony\Component\EventDispatcher\Event;

class ChangePasswordEvent extends Event
{
    /**
     * @var UserPasswordChangeInterface
     */
    private $user;

    /**
     * @param UserPasswordChangeInterface $user
     */
    function __construct(UserPasswordChangeInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return UserPasswordChangeInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
