<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogoutUserListenerSpec extends ObjectBehavior
{
    public function let(
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage,
        AbstractToken $token,
        Request $masterRequest,
        SessionInterface $session
    ): void {
        $tokenStorage->getToken()->willReturn($token);
        $requestStack->getMasterRequest()->willReturn($masterRequest);
        $masterRequest->getSession()->willReturn($session);

        $this->beConstructedWith($requestStack, $tokenStorage);
    }

    public function it_subscribes_to_change_password_event(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            AdminSecurityEvents::CHANGE_PASSWORD => 'onChangePassword',
        ]);
    }

    public function it_logouts_the_user(
        TokenStorageInterface $tokenStorage,
        AbstractToken $token,
        SessionInterface $session,
        ChangePasswordEvent $event,
        UserInterface $user
    ): void {
        $token->getUser()->willReturn($user);
        $event->getUser()->willReturn($user);

        $session->invalidate()->shouldBeCalled();
        $tokenStorage->setToken(null)->shouldBeCalled();

        $this->onChangePassword($event);
    }

    public function it_does_not_logout_the_user_if_it_is_not_currently_logged(
        TokenStorageInterface $tokenStorage,
        AbstractToken $token,
        SessionInterface $session,
        ChangePasswordEvent $event,
        UserInterface $currentUser,
        UserInterface $changedUser
    ): void {
        $token->getUser()->willReturn($currentUser);
        $event->getUser()->willReturn($changedUser);

        $session->invalidate()->shouldNotBeCalled();
        $tokenStorage->setToken(null)->shouldNotBeCalled();

        $this->onChangePassword($event);
    }
}
