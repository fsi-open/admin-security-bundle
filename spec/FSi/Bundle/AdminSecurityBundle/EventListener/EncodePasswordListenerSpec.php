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
use FSi\Bundle\AdminSecurityBundle\Event\UserCreatedEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class EncodePasswordListenerSpec extends ObjectBehavior
{
    public function let(PasswordHasherFactoryInterface $passwordHasherFactory): void
    {
        $this->beConstructedWith($passwordHasherFactory);
    }

    public function it_subscribes_change_password_event(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            ChangePasswordEvent::class => 'onChangePassword',
            UserCreatedEvent::class => 'onUserCreated',
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
        PasswordHasherFactoryInterface $passwordHasherFactory,
        PasswordHasherInterface $hasher,
        ChangePasswordEvent $event,
        UserInterface $user
    ): void {
        $event->getUser()->willReturn($user);
        $user->getPlainPassword()->willReturn('new-password');
        $passwordHasherFactory->getPasswordHasher($user)->willReturn($hasher);
        $hasher->hash('new-password')->willReturn('encoded-new-password');

        $user->setPassword('encoded-new-password')->shouldBeCalled();
        $user->eraseCredentials()->shouldBeCalled();

        $this->onChangePassword($event);
    }

    public function it_encodes_password_for_new_user(
        PasswordHasherFactoryInterface $passwordHasherFactory,
        PasswordHasherInterface $hasher,
        UserCreatedEvent $event,
        UserInterface $user
    ): void {
        $event->getUser()->willReturn($user);
        $user->getPlainPassword()->willReturn('new-password');
        $passwordHasherFactory->getPasswordHasher($user)->willReturn($hasher);
        $hasher->hash('new-password')->willReturn('encoded-new-password');

        $user->setPassword('encoded-new-password')->shouldBeCalled();
        $user->eraseCredentials()->shouldBeCalled();

        $this->onUserCreated($event);
    }
}
