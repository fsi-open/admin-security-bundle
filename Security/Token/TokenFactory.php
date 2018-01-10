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

    public function __construct(int $ttl, int $length = 32)
    {
        $this->ttl = new DateInterval(sprintf('PT%dS', $ttl));
        $this->length = $length;
    }

    public function createToken(): TokenInterface
    {
        return new Token($this->generateToken(), new DateTime(), $this->ttl);
    }

    private function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
