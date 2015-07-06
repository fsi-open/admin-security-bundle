<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Token;

use PhpSpec\ObjectBehavior;

class TokenGeneratorSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Component\Security\Core\Util\SecureRandomInterface $secureRandom
     */
    function let($secureRandom)
    {
        $this->beConstructedWith($secureRandom);
    }

    /**
     * @param \Symfony\Component\Security\Core\Util\SecureRandomInterface $secureRandom
     */
    function it_should_generate_token($secureRandom)
    {
        $secureRandom->nextBytes(32)->willReturn('12345678901234567890123456789012');

        $this->generateToken()->shouldReturn('MTIzNDU2Nzg5MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTI');
    }
}
