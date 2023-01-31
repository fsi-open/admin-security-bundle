<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Event;

use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class PromoteUserEvent extends Event
{
    private UserInterface $user;
    private string $role;

    public function __construct(UserInterface $user, string $role)
    {
        $this->user = $user;
        $this->role = $role;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
