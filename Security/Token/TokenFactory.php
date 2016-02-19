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
use Symfony\Component\Security\Core\Util\SecureRandomInterface;

class TokenFactory implements TokenFactoryInterface
{
    /**
     * @var SecureRandomInterface
     */
    private $secureRandom;

    /**
     * @var DateInterval
     */
    private $ttl;

    /**
     * @var int
     */
    private $length;

    /**
     * @param SecureRandomInterface $secureRandom
     * @param integer $ttl
     * @param integer $length
     */
    public function __construct(SecureRandomInterface $secureRandom, $ttl, $length = 32)
    {
        $this->secureRandom = $secureRandom;
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
