<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserCreatedListenerSpec extends ObjectBehavior
{
    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface $tokenFactory
     */
    function let($tokenFactory)
    {
        $this->beConstructedWith($tokenFactory);
    }

    function it_subscribes_user_created_event()
    {
        $this->getSubscribedEvents()->shouldReturn(array(
            AdminSecurityEvents::USER_CREATED => 'onUserCreated'
        ));
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Event\UserEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserActivableInterface $user
     */
    function it_does_nothing_if_user_is_enabled($event, $user)
    {
        $event->getUser()->willReturn($user);
        $user->isEnabled()->willReturn(true);

        $user->setActivationToken(Argument::any())->shouldNotBeCalled();

        $this->onUserCreated($event);
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface $tokenFactory
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminSecurityBundle\Event\UserEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserActivableInterface $user
     */
    function it_sets_activation_token_if_user_is_not_enabled($tokenFactory, $token, $event, $user)
    {
        $event->getUser()->willReturn($user);
        $user->isEnabled()->willReturn(false);
        $tokenFactory->createToken()->willReturn($token);

        $user->setActivationToken($token)->shouldBeCalled();

        $this->onUserCreated($event);
    }
}
