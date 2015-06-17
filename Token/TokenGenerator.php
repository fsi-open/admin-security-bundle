<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Token;

use Symfony\Component\Security\Core\Util\SecureRandomInterface;

class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * @var SecureRandomInterface
     */
    private $secureRandom;

    public function __construct(SecureRandomInterface $secureRandom)
    {
        $this->secureRandom = $secureRandom;
    }

    /**
     * @return string
     */
    public function generateToken()
    {
        return str_replace(['+', '/', '='], '', base64_encode($this->secureRandom->nextBytes(32)));
    }
}
