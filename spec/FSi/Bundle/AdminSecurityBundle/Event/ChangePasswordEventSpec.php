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
use Symfony\Contracts\EventDispatcher\Event;

class ChangePasswordEventSpec extends ObjectBehavior
{
    public function let(ChangeablePasswordInterface $user): void
    {
        $this->beConstructedWith($user);
    }

    public function it_is_event(): void
    {
        $this->shouldHaveType(Event::class);
    }

    public function it_returns_user(ChangeablePasswordInterface $user): void
    {
        $this->getUser()->shouldReturn($user);
    }
}
