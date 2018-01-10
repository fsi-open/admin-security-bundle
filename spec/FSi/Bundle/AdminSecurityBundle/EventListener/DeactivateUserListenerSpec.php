<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;

class DeactivateUserListenerSpec extends ObjectBehavior
{
    function it_subscribes_deactivation_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            AdminSecurityEvents::DEACTIVATION => 'onDeactivation'
        ]);
    }

    function it_activates_user(ActivationEvent $event, ActivableInterface $user)
    {
        $event->getUser()->willReturn($user);

        $user->setEnabled(false)->shouldBeCalled();

        $this->onDeactivation($event);
    }
}
