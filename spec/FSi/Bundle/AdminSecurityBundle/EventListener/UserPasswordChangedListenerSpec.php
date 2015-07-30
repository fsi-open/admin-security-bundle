<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserPasswordChangedListenerSpec extends ObjectBehavior
{
    function it_subscribes_change_password_event()
    {
        $this->getSubscribedEvents()->shouldReturn(array(
            AdminSecurityEvents::CHANGE_PASSWORD=> 'onChangePassword'
        ));
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserEnforcePasswordChangeInterface $user
     */
    function it_does_nothing_if_has_not_enforced_password_change($event, $user)
    {
        $event->getUser()->willReturn($user);
        $user->hasEnforcedPasswordChange()->willReturn(false);

        $user->enforcePasswordChange(Argument::any())->shouldNotBeCalled();

        $this->onChangePassword($event);
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserEnforcePasswordChangeInterface $user
     */
    function it_ceases_enforced_password_change($event, $user)
    {
        $event->getUser()->willReturn($user);
        $user->hasEnforcedPasswordChange()->willReturn(true);

        $user->enforcePasswordChange(false)->shouldBeCalled();

        $this->onChangePassword($event);
    }
}
