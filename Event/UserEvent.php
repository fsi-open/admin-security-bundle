<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Event;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class UserEvent extends Event
{
    /**
     * @var AdvancedUserInterface
     */
    private $user;

    public function __construct(AdvancedUserInterface $user)
    {
        $this->user = $user;
    }

    public function getUser(): AdvancedUserInterface
    {
        return $this->user;
    }
}
