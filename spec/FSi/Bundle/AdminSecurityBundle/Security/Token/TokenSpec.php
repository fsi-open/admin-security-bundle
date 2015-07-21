<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Security\Token;

use DateInterval;
use DateTime;
use PhpSpec\ObjectBehavior;

class TokenSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('abc', new DateTime(), new DateInterval('PT2M'));
    }

    public function it_returns_token()
    {
        $this->getToken()->shouldReturn('abc');
    }

    public function it_calculates_expiration_time()
    {
        $this->isNonExpired()->shouldReturn(true);
    }

    public function it_expires_token()
    {
        $time3MinutesAgo = new DateTime();
        $time3MinutesAgo->sub(new DateInterval('PT3M'));
        $this->beConstructedWith('abc', $time3MinutesAgo, new DateInterval('PT2M'));

        $this->isNonExpired()->shouldReturn(false);
    }
}
