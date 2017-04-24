<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Doctrine\Admin;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PasswordResetBatchElementSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith([], 'FQCN\User\Model', $eventDispatcher);
    }

    function it_is_batch_element()
    {
        $this->shouldHaveType('FSi\Bundle\AdminBundle\Doctrine\Admin\BatchElement');
    }

    function it_should_return_class_name()
    {
        $this->getClassName()->shouldReturn('FQCN\User\Model');
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
            Argument::type('FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent')
        ))->shouldBeCalled();

        $this->apply($user);
    }
}
