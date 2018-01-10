<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\Doctrine\Admin;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\User;
use FSi\Bundle\AdminBundle\Doctrine\Admin\BatchElement;
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PasswordResetBatchElementSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith([], User::class, $eventDispatcher);
    }

    function it_is_batch_element()
    {
        $this->shouldHaveType(BatchElement::class);
    }

    function it_should_return_class_name()
    {
        $this->getClassName()->shouldReturn(User::class);
    }

    function it_should_return_id()
    {
        $this->getId()->shouldReturn('admin_security_password_reset');
    }

    function it_should_dispatch_password_reset_event(
        ResettablePasswordInterface $user,
        EventDispatcherInterface $eventDispatcher
    ) {
        $eventDispatcher->dispatch(AdminSecurityEvents::RESET_PASSWORD_REQUEST, Argument::allOf(
            Argument::type(ResetPasswordRequestEvent::class)
        ))->shouldBeCalled();

        $this->apply($user);
    }
}
