<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\DeactivationEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use PhpSpec\ObjectBehavior;

class DeactivateUserListenerSpec extends ObjectBehavior
{
    public function it_subscribes_deactivation_event(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            DeactivationEvent::class => 'onDeactivation',
        ]);
    }

    public function it_activates_user(DeactivationEvent $event, ActivableInterface $user): void
    {
        $event->getUser()->willReturn($user);
        $user->setEnabled(false)->shouldBeCalled();

        $this->onDeactivation($event);
    }
}
