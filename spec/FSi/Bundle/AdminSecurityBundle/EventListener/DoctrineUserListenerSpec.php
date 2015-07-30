<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class DoctrineUserListenerSpec extends ObjectBehavior
{
    /**
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $registry
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    function let($registry, $objectManager)
    {
        $this->beConstructedWith($registry);
        $registry->getManagerForClass(Argument::any())->willReturn($objectManager);
    }

    function it_subscribes_all_events()
    {
        $this->getSubscribedEvents()->shouldReturn(array(
            AdminSecurityEvents::CHANGE_PASSWORD => 'onChangePassword',
            AdminSecurityEvents::RESET_PASSWORD_REQUEST => 'onResetPasswordRequest',
            AdminSecurityEvents::ACTIVATION => 'onActivation',
            AdminSecurityEvents::DEACTIVATION => 'onDeactivation',
            AdminSecurityEvents::USER_CREATED => 'onUserCreated',
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin'
        ));
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent $event
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    function it_flushes_om_after_changing_password($event, $objectManager)
    {
        $user = new User();

        $event->getUser()->willReturn($user);

        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onChangePassword($event);
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent $event
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    function it_flushes_om_after_requesting_change_of_password($event, $objectManager)
    {
        $user = new User();

        $event->getUser()->willReturn($user);

        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onResetPasswordRequest($event);
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent $event
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    function it_flushes_om_after_activation($event, $objectManager)
    {
        $user = new User();

        $event->getUser()->willReturn($user);

        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onActivation($event);
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent $event
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    function it_flushes_om_after_deactivation($event, $objectManager)
    {
        $user = new User();

        $event->getUser()->willReturn($user);

        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onDeactivation($event);
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\UserEvent $event
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    function it_flushes_om_after_user_creation($event, $objectManager)
    {
        $user = new User();

        $event->getUser()->willReturn($user);

        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onUserCreated($event);
    }

    /**
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    function it_flushes_om_after_user_logged_in($event, $token, $objectManager)
    {
        $user = new User();
        $token->getUser()->willReturn($user);
        $event->getAuthenticationToken()->willReturn($token);

        $objectManager->persist($user)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->onInteractiveLogin($event);
    }
}
