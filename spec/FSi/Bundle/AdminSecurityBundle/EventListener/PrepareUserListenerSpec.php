<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\FormEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Util\SecureRandomInterface;

class PrepareUserListenerSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $eventDispatcher, SecureRandomInterface $secureRandom)
    {
        $this->beConstructedWith($eventDispatcher, $secureRandom);
    }

    function it_is_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_should_prepare_new_user_and_dispatch_user_created(
        FormEvent $event,
        FormInterface $form,
        UserInterface $user,
        EventDispatcherInterface $eventDispatcher,
        SecureRandomInterface $secureRandom
    ) {
        $event->getForm()->willReturn($form);
        $form->getData()->willReturn($user);

        $user->getPassword()->willReturn(null);

        $secureRandom->nextBytes(32)->willReturn('secure random bytes');
        $user->setPlainPassword('secure random bytes')->shouldBeCalled();
        $user->setEnabled(false)->shouldBeCalled();
        $user->enforcePasswordChange(true)->shouldBeCalled();

        $eventDispatcher->dispatch(
            AdminSecurityEvents::USER_CREATED,
            Argument::allOf(
                Argument::type('\FSi\Bundle\AdminSecurityBundle\Event\UserEvent')
            )
        )->shouldBeCalled();

        $this->prepareAndDispatchUserCreated($event);
    }

    function it_should_do_not_set_random_password_when_user_already_exists(
        FormEvent $event,
        FormInterface $form,
        UserInterface $user,
        EventDispatcherInterface $eventDispatcher
    ) {
        $event->getForm()->willReturn($form);
        $form->getData()->willReturn($user);

        $user->getPassword()->willReturn('password-hash');

        $user->setPlainPassword(Argument::any())->shouldNotBeCalled();
        $user->setEnabled(Argument::any())->shouldNotBeCalled();
        $user->enforcePasswordChange(Argument::any())->shouldNotBeCalled();

        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $this->prepareAndDispatchUserCreated($event);
    }
}
