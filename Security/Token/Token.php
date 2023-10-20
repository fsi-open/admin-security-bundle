<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Security\Token;

use DateInterval;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;

class Token implements TokenInterface
{
    private string $token;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $expiresAt;

    public function __construct(string $token, ClockInterface $clock, DateInterval $ttl)
    {
        $this->token = $token;
        $this->createdAt = $clock->now();
        $this->expiresAt = $clock->now()->add($ttl);
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isNonExpired(ClockInterface $clock): bool
    {
        return $clock->now() <= $this->expiresAt;
    }
}
