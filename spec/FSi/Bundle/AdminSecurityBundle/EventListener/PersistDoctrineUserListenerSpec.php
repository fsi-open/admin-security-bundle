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
use Doctrine\Common\Persistence\ObjectManager;
use FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class PersistDoctrineUserListenerSpec extends ObjectBehavior
{
    function let(Registry $registry, ObjectManager $objectManager)
    {
        $this->beConstructedWith($registry);
        $registry->getManagerForClass(Argument::any())->willReturn($objectManager);
    }

    function it_subscribes_all_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            AdminSecurityEvents::CHANGE_PASSWORD => 'onChangePassword',
            AdminSecurityEvents::RESET_PASSWORD_REQUEST => 'onResetPasswordRequest',
            AdminSecurityEvents::ACTIVATION => 'onActivation',
            AdminSecurityEvents::DEACTIVATION => 'onDeactivation',
            AdminSecurityEvents::USER_CREATED => 'onUserCreated',
            AdminSecurityEvents::PROMOTE_USER => 'onPromoteUser',
            AdminSecurityEvents::DEMOTE_USER => 'onDemoteUser',
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin'
        ]);
    }

    function it_flushes_om_after_changing_password(
        ChangePasswordEvent $event,
        ObjectManager $objectManager,
        User $user
    ) {
        $event->getUser()->willReturn($user);

        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onChangePassword($event);
    }

    function it_flushes_om_after_requesting_change_of_password(
        ResetPasswordRequestEvent $event,
        ObjectManager $objectManager,
        User $user
    ) {
        $event->getUser()->willReturn($user);

        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onResetPasswordRequest($event);
    }

    function it_flushes_om_after_activation(
        ActivationEvent $event,
        ObjectManager $objectManager,
        User $user
    ) {
        $event->getUser()->willReturn($user);

        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onActivation($event);
    }

    function it_flushes_om_after_deactivation(
        ActivationEvent $event,
        ObjectManager $objectManager,
        User $user
    ) {
        $event->getUser()->willReturn($user);

        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onDeactivation($event);
    }

    function it_flushes_om_after_user_creation(
        UserEvent $event,
        ObjectManager $objectManager,
        User $user
    ) {
        $event->getUser()->willReturn($user);

        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onUserCreated($event);
    }

    function it_flushes_om_after_promote_user(
        UserEvent $event,
        ObjectManager $objectManager,
        User $user
    ) {
        $event->getUser()->willReturn($user);

        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onPromoteUser($event);
    }

    function it_flushes_om_after_demote_user(
        UserEvent $event,
        ObjectManager $objectManager,
        User $user
    ) {
        $event->getUser()->willReturn($user);

        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onDemoteUser($event);
    }

    function it_flushes_om_after_user_logged_in(
        InteractiveLoginEvent $event,
        TokenInterface $token,
        ObjectManager $objectManager,
        User $user
    ) {
        $token->getUser()->willReturn($user);
        $event->getAuthenticationToken()->willReturn($token);

        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onInteractiveLogin($event);
    }
}
