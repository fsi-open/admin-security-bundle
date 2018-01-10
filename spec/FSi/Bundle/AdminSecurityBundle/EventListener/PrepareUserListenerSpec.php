<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\FormEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;

class PrepareUserListenerSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith($eventDispatcher);
    }

    function it_is_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_should_prepare_new_user_and_dispatch_user_created(
        FormEvent $event,
        FormInterface $form,
        UserInterface $user,
        EventDispatcherInterface $eventDispatcher
    ) {
        $event->getForm()->willReturn($form);
        $form->getData()->willReturn($user);

        $user->getPassword()->willReturn(null);

        $user->setPlainPassword(Argument::type('string'))->shouldBeCalled();
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
