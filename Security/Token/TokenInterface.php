<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Security\Token;

use Psr\Clock\ClockInterface;

interface TokenInterface
{
    public function getToken(): string;
    public function isNonExpired(ClockInterface $clock): bool;
}
