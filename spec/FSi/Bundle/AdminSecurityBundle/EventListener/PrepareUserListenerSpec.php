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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;

class PrepareUserListenerSpec extends ObjectBehavior
{
    public function let(EventDispatcherInterface $eventDispatcher): void
    {
        $this->beConstructedWith($eventDispatcher);
    }

    public function it_is_event_subscriber(): void
    {
        $this->shouldHaveType(EventSubscriberInterface::class);
    }

    public function it_should_prepare_new_user_and_dispatch_user_created(
        FormEvent $event,
        FormInterface $form,
        UserInterface $user,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $event->getForm()->willReturn($form);
        $form->getData()->willReturn($user);

        $user->getPassword()->willReturn(null);

        $user->setPlainPassword(Argument::type('string'))->shouldBeCalled();
        $user->setEnabled(false)->shouldBeCalled();
        $user->enforcePasswordChange(true)->shouldBeCalled();

        $eventDispatcher->dispatch(
            Argument::type(UserEvent::class),
            AdminSecurityEvents::USER_CREATED
        )->shouldBeCalled();

        $this->prepareAndDispatchUserCreated($event);
    }

    public function it_should_do_not_set_random_password_when_user_already_exists(
        FormEvent $event,
        FormInterface $form,
        UserInterface $user,
        EventDispatcherInterface $eventDispatcher
    ): void {
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
