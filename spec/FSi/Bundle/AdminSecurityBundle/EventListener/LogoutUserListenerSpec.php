<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LogoutUserListenerSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\HttpFoundation\Request $masterRequest
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    function let($requestStack, $tokenStorage, $masterRequest, $session)
    {
        $requestStack->getMasterRequest()->willReturn($masterRequest);
        $masterRequest->getSession()->willReturn($session);

        $this->beConstructedWith($requestStack, $tokenStorage);
    }

    function it_subscribes_to_change_password_event()
    {
        $this->getSubscribedEvents()->shouldReturn(array(
            AdminSecurityEvents::CHANGE_PASSWORD => 'onChangePassword'
        ));
    }

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     */
    function it_logouts_the_user($tokenStorage, $token, $session, $event, $user)
    {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $event->getUser()->willReturn($user);

        $session->invalidate()->shouldBeCalled();
        $tokenStorage->setToken(null)->shouldBeCalled();

        $this->onChangePassword($event);
    }

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $currentUser
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $changedUser
     */
    function it_does_not_logout_the_user_if_it_is_not_currently_logged(
        $tokenStorage, $token, $session, $event, $currentUser, $changedUser
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($currentUser);
        $event->getUser()->willReturn($changedUser);

        $session->invalidate()->shouldNotBeCalled();
        $tokenStorage->setToken(null)->shouldNotBeCalled();

        $this->onChangePassword($event);
    }
}
