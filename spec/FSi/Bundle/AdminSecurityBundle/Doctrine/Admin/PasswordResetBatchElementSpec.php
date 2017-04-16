<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Doctrine\Admin;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PasswordResetBatchElementSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    function let($eventDispatcher)
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

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface $user
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    function it_should_dispatch_password_reset_event($user, $eventDispatcher)
    {
        $eventDispatcher->dispatch(AdminSecurityEvents::RESET_PASSWORD_REQUEST, Argument::allOf(
            Argument::type('FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent')
        ))->shouldBeCalled();

        $this->apply($user);
    }
}
