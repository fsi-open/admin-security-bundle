<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\EnforceablePasswordChangeInterface;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ClearChangePasswordEnforcementListenerSpec extends ObjectBehavior
{
    public function it_subscribes_change_password_event(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            AdminSecurityEvents::CHANGE_PASSWORD => 'onChangePassword',
        ]);
    }

    public function it_does_nothing_if_has_not_enforced_password_change(
        ChangePasswordEvent $event,
        EnforceablePasswordChangeInterface $user
    ): void {
        $event->getUser()->willReturn($user);
        $user->isForcedToChangePassword()->willReturn(false);

        $user->enforcePasswordChange(Argument::any())->shouldNotBeCalled();

        $this->onChangePassword($event);
    }

    public function it_ceases_enforced_password_change(
        ChangePasswordEvent $event,
        EnforceablePasswordChangeInterface $user
    ): void {
        $event->getUser()->willReturn($user);
        $user->isForcedToChangePassword()->willReturn(true);

        $user->enforcePasswordChange(false)->shouldBeCalled();

        $this->onChangePassword($event);
    }
}
