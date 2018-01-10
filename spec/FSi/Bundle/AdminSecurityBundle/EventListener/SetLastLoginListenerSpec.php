<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SetLastLoginListenerSpec extends ObjectBehavior
{
    function it_subscribes_for_interactive_login_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
                SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin'
        ]);
    }

    function it_sets_last_login(
        InteractiveLoginEvent $event,
        TokenInterface $token,
        UserInterface $user
    ) {
        $event->getAuthenticationToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $user->setLastLogin(Argument::type('DateTime'))->shouldBeCalled();

        $this->onInteractiveLogin($event);
    }
}
