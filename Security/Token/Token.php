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
use DateTime;

class Token implements TokenInterface
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var DateTime
     */
    protected $expiresAt;

    public function __construct(string $token, DateTime $createdAt, DateInterval $ttl)
    {
        $this->token = $token;
        $this->createdAt = clone $createdAt;
        $this->expiresAt = clone $this->createdAt;
        $this->expiresAt->add($ttl);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function isNonExpired(): bool
    {
        return new DateTime() <= $this->expiresAt;
    }
}
