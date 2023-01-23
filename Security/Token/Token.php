<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Security\Token;

use Assert\Assertion;
use DateInterval;
use DateTimeImmutable;

class Token implements TokenInterface
{
    private ?string $token = null;
    private ?DateTimeImmutable $createdAt = null;
    private ?DateTimeImmutable $expiresAt = null;

    public function __construct(string $token, DateTimeImmutable $createdAt, DateInterval $ttl)
    {
        $this->token = $token;
        $this->createdAt = $createdAt;
        $this->expiresAt = $createdAt->add($ttl);
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        Assertion::notNull($this->createdAt);
        return $this->createdAt;
    }

    public function getToken(): string
    {
        Assertion::notNull($this->token);
        return $this->token;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        Assertion::notNull($this->expiresAt);
        return $this->expiresAt;
    }

    public function isNonExpired(): bool
    {
        Assertion::notNull($this->expiresAt);
        return new DateTimeImmutable() <= $this->expiresAt;
    }
}
