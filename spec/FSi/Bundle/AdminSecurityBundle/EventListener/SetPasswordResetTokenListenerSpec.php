<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordTokenSetEvent;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\EventDispatcher\EventDispatcherInterface;

class SetPasswordResetTokenListenerSpec extends ObjectBehavior
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
            ResetPasswordRequestEvent::class => 'setPasswordResetToken'
        ]);
    }

    public function it_sends_email(
        TokenFactoryInterface $tokenFactory,
        TokenInterface $token,
        ResetPasswordRequestEvent $event,
        ResettablePasswordInterface $user,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $event->getUser()->willReturn($user);
        $tokenFactory->createToken()->willReturn($token);

        $user->setPasswordResetToken($token)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::type(ResetPasswordTokenSetEvent::class))->shouldBeCalled();

        $this->setPasswordResetToken($event);
    }
}
