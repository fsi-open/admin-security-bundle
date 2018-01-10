<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\Event;

use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\Event;

class ChangePasswordEventSpec extends ObjectBehavior
{
    function let(ChangeablePasswordInterface $user)
    {
        $this->beConstructedWith($user);
    }

    function it_is_event()
    {
        $this->shouldHaveType(Event::class);
    }

    function it_returns_user(ChangeablePasswordInterface $user)
    {
        $this->getUser()->shouldReturn($user);
    }
}
