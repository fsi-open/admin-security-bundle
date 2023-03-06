<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ObjectManager;
use FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Event\DeactivationEvent;
use FSi\Bundle\AdminSecurityBundle\Event\DemoteUserEvent;
use FSi\Bundle\AdminSecurityBundle\Event\PromoteUserEvent;
use FSi\Bundle\AdminSecurityBundle\Event\ResendActivationTokenEvent;
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use FSi\Bundle\AdminSecurityBundle\Event\UserCreatedEvent;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class PersistDoctrineUserListenerSpec extends ObjectBehavior
{
    public function let(Registry $registry, ObjectManager $objectManager): void
    {
        $this->beConstructedWith($registry);
        $registry->getManagerForClass(Argument::any())->willReturn($objectManager);
    }

    public function it_subscribes_all_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            ChangePasswordEvent::class => 'onChangePassword',
            ResetPasswordRequestEvent::class => 'onResetPasswordRequest',
            ActivationEvent::class => 'onActivation',
            ResendActivationTokenEvent::class => 'onActivationResend',
            DeactivationEvent::class => 'onDeactivation',
            UserCreatedEvent::class => 'onUserCreated',
            PromoteUserEvent::class => 'onPromoteUser',
            DemoteUserEvent::class => 'onDemoteUser',
            InteractiveLoginEvent::class => 'onInteractiveLogin'
        ]);
    }

    public function it_flushes_om_after_changing_password(
        ChangePasswordEvent $event,
        ObjectManager $objectManager,
        User $user
    ): void {
        $event->getUser()->willReturn($user);

        $objectManager->contains($user)->willReturn(false);
        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onChangePassword($event);
    }

    public function it_flushes_om_after_requesting_change_of_password(
        ResetPasswordRequestEvent $event,
        ObjectManager $objectManager,
        User $user
    ): void {
        $event->getUser()->willReturn($user);

        $objectManager->contains($user)->willReturn(false);
        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onResetPasswordRequest($event);
    }

    public function it_flushes_om_after_activation(
        ActivationEvent $event,
        ObjectManager $objectManager,
        User $user
    ): void {
        $event->getUser()->willReturn($user);

        $objectManager->contains($user)->willReturn(false);
        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onActivation($event);
    }

    public function it_flushes_om_after_resending_activation_token(
        ResendActivationTokenEvent $event,
        ObjectManager $objectManager,
        User $user
    ): void {
        $event->getUser()->willReturn($user);

        $objectManager->contains($user)->willReturn(false);
        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onActivationResend($event);
    }

    public function it_flushes_om_after_deactivation(
        DeactivationEvent $event,
        ObjectManager $objectManager,
        User $user
    ): void {
        $event->getUser()->willReturn($user);

        $objectManager->contains($user)->willReturn(false);
        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onDeactivation($event);
    }

    public function it_flushes_om_after_user_creation(
        UserCreatedEvent $event,
        ObjectManager $objectManager,
        User $user
    ): void {
        $event->getUser()->willReturn($user);

        $objectManager->contains($user)->willReturn(false);
        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onUserCreated($event);
    }

    public function it_flushes_om_after_promote_user(
        PromoteUserEvent $event,
        ObjectManager $objectManager,
        User $user
    ): void {
        $event->getUser()->willReturn($user);

        $objectManager->contains($user)->willReturn(false);
        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onPromoteUser($event);
    }

    public function it_flushes_om_after_demote_user(
        DemoteUserEvent $event,
        ObjectManager $objectManager,
        User $user
    ): void {
        $event->getUser()->willReturn($user);

        $objectManager->contains($user)->willReturn(false);
        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onDemoteUser($event);
    }

    public function it_flushes_om_after_user_logged_in(ObjectManager $objectManager, UsernamePasswordToken $token): void
    {
        $user = new User();
        $objectManager->contains($user)->willReturn(false);
        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $token->getUser()->willReturn($user);

        $this->onInteractiveLogin(
            new InteractiveLoginEvent(new Request(), $token->getWrappedObject())
        );
    }
}
