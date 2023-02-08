<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\UserCreatedEvent;
use FSi\Bundle\AdminSecurityBundle\EventListener\SetEmailAsUsernameListener;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use PhpSpec\ObjectBehavior;

class SetEmailAsUsernameListenerSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SetEmailAsUsernameListener::class);
    }

    public function it_should_set_email_as_username(UserCreatedEvent $event, UserInterface $user): void
    {
        $event->getUser()->willReturn($user);

        $user->getEmail()->willReturn('test@example.com');
        $user->setUsername('test@example.com')->shouldBeCalled();

        $this->setEmailAsUsername($event);
    }
}
