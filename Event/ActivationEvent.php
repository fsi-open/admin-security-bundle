<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Event;

use FSi\Bundle\AdminSecurityBundle\Security\User\UserActivableInterface;
use Symfony\Component\EventDispatcher\Event;

class ActivationEvent extends Event
{
    /**
     * @var UserActivableInterface
     */
    private $user;

    /**
     * @param UserActivableInterface $user
     */
    function __construct(UserActivableInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return UserActivableInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
