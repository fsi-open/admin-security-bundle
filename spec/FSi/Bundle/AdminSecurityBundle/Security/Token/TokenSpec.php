<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

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
