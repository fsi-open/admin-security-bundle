<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PrepareUserListenerSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Symfony\Component\Security\Core\Util\SecureRandomInterface $secureRandom
     */
    function let($eventDispatcher, $secureRandom)
    {
        $this->beConstructedWith($eventDispatcher, $secureRandom);
    }

    function it_is_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    /**
     * @param \FSi\Bundle\AdminBundle\Event\FormEvent $event
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Symfony\Component\Security\Core\Util\SecureRandomInterface $secureRandom
     */
    function it_should_prepare_new_user_and_dispatch_user_created($event, $form, $user, $eventDispatcher, $secureRandom)
    {
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

    /**
     * @param \FSi\Bundle\AdminBundle\Event\FormEvent $event
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     */
    function it_should_do_not_set_random_password_when_user_already_exists($event, $form, $user, $eventDispatcher)
    {
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
