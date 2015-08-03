<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Event;

use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use Symfony\Component\EventDispatcher\Event;

class ActivationEvent extends Event
{
    /**
     * @var ActivableInterface
     */
    private $user;

    /**
     * @param ActivableInterface $user
     */
    function __construct(ActivableInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return ActivableInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
