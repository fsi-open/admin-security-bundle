<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\ActivationTokenSetEvent;
use FSi\Bundle\AdminSecurityBundle\Event\UserCreatedEvent;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;

class SetActivationTokenListenerSpec extends ObjectBehavior
{
    public function let(
        TokenFactoryInterface $tokenFactory,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $this->beConstructedWith($tokenFactory, $eventDispatcher);
    }

    public function it_subscribes_user_created_event(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            UserCreatedEvent::class => 'onUserCreated',
        ]);
    }

    public function it_sends_email_if_user_is_not_enabled(
        EventDispatcherInterface $eventDispatcher,
        TokenFactoryInterface $tokenFactory,
        TokenInterface $token,
        UserCreatedEvent $event,
        User $user
    ): void {
        $user->isEnabled()->willReturn(false);
        $event->getUser()->willReturn($user);
        $tokenFactory->createToken()->willReturn($token);

        $user->setActivationToken($token)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(ActivationTokenSetEvent::class))->shouldBeCalled();

        $this->onUserCreated($event);
    }

    public function it_does_not_send_email_if_user_is_enabled(
        EventDispatcherInterface $eventDispatcher,
        UserCreatedEvent $event,
        User $user
    ): void {
        $user->isEnabled()->willReturn(true);
        $event->getUser()->willReturn($user);

        $eventDispatcher->dispatch(Argument::type(ActivationTokenSetEvent::class))->shouldNotBeCalled();

        $this->onUserCreated($event);
    }
}
