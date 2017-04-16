<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\EnforceablePasswordChangeInterface;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ClearChangePasswordEnforcementListenerSpec extends ObjectBehavior
{
    function it_subscribes_change_password_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            AdminSecurityEvents::CHANGE_PASSWORD=> 'onChangePassword'
        ]);
    }

    function it_does_nothing_if_has_not_enforced_password_change(
        ChangePasswordEvent $event,
        EnforceablePasswordChangeInterface $user
    ) {
        $event->getUser()->willReturn($user);
        $user->isForcedToChangePassword()->willReturn(false);

        $user->enforcePasswordChange(Argument::any())->shouldNotBeCalled();

        $this->onChangePassword($event);
    }

    function it_ceases_enforced_password_change(
        ChangePasswordEvent $event,
        EnforceablePasswordChangeInterface $user
    ) {
        $event->getUser()->willReturn($user);
        $user->isForcedToChangePassword()->willReturn(true);

        $user->enforcePasswordChange(false)->shouldBeCalled();

        $this->onChangePassword($event);
    }
}
