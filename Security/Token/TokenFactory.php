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
use Psr\Clock\ClockInterface;

use function base64_encode;
use function random_bytes;
use function rtrim;
use function sprintf;
use function strtr;

class TokenFactory implements TokenFactoryInterface
{
    private ClockInterface $clock;
    private DateInterval $ttl;
    /**
     * @var int<1, max>
     */
    private int $length;

    /**
     * @param int<1, max> $ttl
     * @param int<1, max> $length
     */
    public function __construct(ClockInterface $clock, int $ttl, int $length = 32)
    {
        $this->ttl = new DateInterval(sprintf('PT%dS', $ttl));
        $this->length = $length;
        $this->clock = $clock;
    }

    public function createToken(): TokenInterface
    {
        return new Token($this->generateToken(), $this->clock, $this->ttl);
    }

    private function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes($this->length)), '+/', '-_'), '=');
    }
}
