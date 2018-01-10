<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Event;

use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;
use Symfony\Component\EventDispatcher\Event;

class ResetPasswordRequestEvent extends Event
{
    /**
     * @var ResettablePasswordInterface
     */
    private $user;

    public function __construct(ResettablePasswordInterface $user)
    {
        $this->user = $user;
    }

    public function getUser(): ResettablePasswordInterface
    {
        return $this->user;
    }
}
