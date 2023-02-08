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
use FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;
use PhpSpec\ObjectBehavior;

class SendPasswordResetMailListenerSpec extends ObjectBehavior
{
    public function let(
        MailerInterface $mailer,
        TokenFactoryInterface $tokenFactory
    ): void {
        $this->beConstructedWith($mailer, $tokenFactory);
    }

    public function it_subscribes_user_created_event(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            ResetPasswordRequestEvent::class => 'onResetPasswordRequest',
        ]);
    }

    public function it_sends_email(
        MailerInterface $mailer,
        TokenFactoryInterface $tokenFactory,
        TokenInterface $token,
        ResetPasswordRequestEvent $event,
        ResettablePasswordInterface $user
    ): void {
        $event->getUser()->willReturn($user);
        $tokenFactory->createToken()->willReturn($token);

        $user->setPasswordResetToken($token)->shouldBeCalled();
        $mailer->send($user)->shouldBeCalled();

        $this->onResetPasswordRequest($event);
    }
}
