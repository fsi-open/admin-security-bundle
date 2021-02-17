<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class EncodePasswordListenerSpec extends ObjectBehavior
{
    public function let(EncoderFactoryInterface $encoderFactory): void
    {
        $this->beConstructedWith($encoderFactory);
    }

    public function it_subscribes_change_password_event(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            AdminSecurityEvents::CHANGE_PASSWORD => 'onChangePassword',
            AdminSecurityEvents::USER_CREATED => 'onUserCreated',
        ]);
    }

    public function it_does_nothing_if_plain_password_is_not_set(
        ChangePasswordEvent $event,
        ChangeablePasswordInterface $user
    ): void {
        $event->getUser()->willReturn($user);
        $user->getPlainPassword()->willReturn(null);

        $user->setPassword(Argument::any())->shouldNotBeCalled();

        $this->onChangePassword($event);
    }

    public function it_encodes_password_for_user(
        EncoderFactoryInterface $encoderFactory,
        PasswordEncoderInterface $encoder,
        ChangePasswordEvent $event,
        UserInterface $user
    ): void {
        $event->getUser()->willReturn($user);
        $user->getPlainPassword()->willReturn('new-password');
        $encoderFactory->getEncoder($user)->willReturn($encoder);
        $user->getSalt()->willReturn('salt');
        $encoder->encodePassword('new-password', 'salt')->willReturn('encoded-new-password');

        $user->setPassword('encoded-new-password')->shouldBeCalled();
        $user->eraseCredentials()->shouldBeCalled();

        $this->onChangePassword($event);
    }

    public function it_encodes_password_for_new_user(
        EncoderFactoryInterface $encoderFactory,
        PasswordEncoderInterface $encoder,
        UserEvent $event,
        UserInterface $user
    ): void {
        $event->getUser()->willReturn($user);
        $user->getPlainPassword()->willReturn('new-password');
        $encoderFactory->getEncoder($user)->willReturn($encoder);
        $user->getSalt()->willReturn('salt');
        $encoder->encodePassword('new-password', 'salt')->willReturn('encoded-new-password');

        $user->setPassword('encoded-new-password')->shouldBeCalled();
        $user->eraseCredentials()->shouldBeCalled();

        $this->onUserCreated($event);
    }
}
