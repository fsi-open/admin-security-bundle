<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Security\Token;

use PhpSpec\ObjectBehavior;

class TokenFactorySpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Component\Security\Core\Util\SecureRandomInterface $secureRandom
     */
    function let($secureRandom)
    {
        $this->beConstructedWith($secureRandom, 86400, 32);
    }

    /**
     * @param \Symfony\Component\Security\Core\Util\SecureRandomInterface $secureRandom
     */
    function it_should_generate_token($secureRandom)
    {
        $secureRandom->nextBytes(32)->willReturn('12345678901234567890123456789012');

        $token = $this->createToken();
        $token->getToken()->shouldReturn('MTIzNDU2Nzg5MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTI');
    }
}
