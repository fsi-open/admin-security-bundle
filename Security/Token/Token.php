<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    /**
     * @param $token
     * @param DateTime $createdAt
     * @param DateInterval $ttl
     */
    public function __construct($token, DateTime $createdAt, DateInterval $ttl)
    {
        $this->token = $token;
        $this->createdAt = clone $createdAt;
        $this->expiresAt = clone $this->createdAt;
        $this->expiresAt->add($ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * {@inheritdoc}
     */
    public function isNonExpired()
    {
        return new DateTime() <= $this->expiresAt;
    }
}
