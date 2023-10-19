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
use FSi\Bundle\AdminSecurityBundle\DateTime\Clock;

class Token implements TokenInterface
{
    private string $token;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $expiresAt;

    public function __construct(string $token, DateInterval $ttl)
    {
        $this->token = $token;
        $this->createdAt = Clock::now();
        $this->expiresAt = Clock::now()->add($ttl);
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

    public function isNonExpired(): bool
    {
        return Clock::now() <= $this->expiresAt;
    }
}
