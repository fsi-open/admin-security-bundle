<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Event;

use FSi\Bundle\AdminSecurityBundle\Security\Model\ResettablePasswordInterface;
use Symfony\Component\EventDispatcher\Event;

class ResetPasswordRequestEvent extends Event
{
    /**
     * @var ResettablePasswordInterface
     */
    private $user;

    /**
     * @param ResettablePasswordInterface $user
     */
    function __construct(ResettablePasswordInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return ResettablePasswordInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
