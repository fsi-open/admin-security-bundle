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

class Token implements TokenInterface
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var DateTimeImmutable
     */
    protected $createdAt;

    /**
     * @var DateTimeImmutable
     */
    protected $expiresAt;

    public function __construct(string $token, DateTimeImmutable $createdAt, DateInterval $ttl)
    {
        $this->token = $token;
        $this->createdAt = $createdAt;
        $this->expiresAt = $createdAt->add($ttl);
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
        return new DateTimeImmutable() <= $this->expiresAt;
    }
}
