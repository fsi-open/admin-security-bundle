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

class TokenFactory implements TokenFactoryInterface
{
    /**
     * @var DateInterval
     */
    private $ttl;

    /**
     * @var int
     */
    private $length;

    /**
     * @param integer $ttl
     * @param integer $length
     */
    public function __construct($ttl, $length = 32)
    {
        $this->ttl = new DateInterval(sprintf('PT%dS', $ttl));
        $this->length = $length;
    }

    /**
     * @return TokenInterface
     */
    public function createToken()
    {
        return new Token($this->generateToken(), new DateTime(), $this->ttl);
    }

    /**
     * @return string
     */
    private function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
