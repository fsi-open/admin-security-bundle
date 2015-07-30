<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Event;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\EventDispatcher\Event;

class UserEvent extends Event
{
    /**
     * @var AdvancedUserInterface
     */
    private $user;

    /**
     * @param AdvancedUserInterface $user
     */
    function __construct(AdvancedUserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return AdvancedUserInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
