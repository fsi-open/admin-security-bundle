<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Security\UserChecker;

use FSi\Bundle\AdminSecurityBundle\Security\User\UserIdentifierHelper;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

final class DisabledUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(SymfonyUserInterface $user): void
    {
        if (false === $user instanceof UserInterface) {
            return;
        }

        if (true === $user->isEnabled()) {
            return;
        }

        $userIdentifier = UserIdentifierHelper::getUserIdentifier($user);

        throw new LockedException("User {$userIdentifier} is disabled");
    }

    public function checkPostAuth(SymfonyUserInterface $user): void
    {
    }
}
