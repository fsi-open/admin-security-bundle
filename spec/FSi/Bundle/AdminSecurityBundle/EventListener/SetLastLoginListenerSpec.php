<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Http\SecurityEvents;

class SetLastLoginListenerSpec extends ObjectBehavior
{
    function it_subscribes_for_interactive_login_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
                SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin'
        ]);
    }

    /**
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     */
    function it_sets_last_login($event, $token, $user)
    {
        $event->getAuthenticationToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $user->setLastLogin(Argument::type('DateTime'))->shouldBeCalled();

        $this->onInteractiveLogin($event);
    }
}
