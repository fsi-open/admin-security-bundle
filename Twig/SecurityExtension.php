<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Twig;

use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SecurityExtension extends AbstractExtension
{
    /**
     * @return array<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'are_same_users',
                static function (UserInterface $a, UserInterface $b): bool {
                    if (false === $a instanceof $b && false === $b instanceof $a) {
                        return false;
                    }

                    return $a->getUsername() === $b->getUsername();
                }
            )
        ];
    }
}
