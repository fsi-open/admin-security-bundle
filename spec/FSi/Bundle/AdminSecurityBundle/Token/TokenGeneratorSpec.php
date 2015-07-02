<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Token;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Util\SecureRandomInterface;

class TokenGeneratorSpec extends ObjectBehavior
{
    function let(SecureRandomInterface $secureRandom)
    {
        $this->beConstructedWith($secureRandom);
    }

    function it_should_generate_token(SecureRandomInterface $secureRandom)
    {
        $secureRandom->nextBytes(32)->willReturn('12345678901234567890123456789012');

        $this->generateToken()->shouldReturn('MTIzNDU2Nzg5MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTI');
    }
}
