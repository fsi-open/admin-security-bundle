<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Security\User;

use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

use function method_exists;

/**
 * @internal
 */
final class UserIdentifierHelper
{
    public static function getUserIdentifier(SymfonyUserInterface $user): string
    {
        if (true === method_exists($user, 'getUserIdentifier')) {
            return $user->getUserIdentifier();
        }

        if (true === method_exists($user, 'getUsername')) {
            return $user->getUsername();
        }

        throw new RuntimeException('Unable to identify User object');
    }

    public static function getTokenUserIdentifier(TokenInterface $token): string
    {
        if (true === method_exists($token, 'getUserIdentifier')) {
            return $token->getUserIdentifier();
        }

        if (true === method_exists($token, 'getUsername')) {
            return $token->getUsername();
        }

        throw new RuntimeException('Unable to identify User object');
    }
}
