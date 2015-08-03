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
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent $event
     */
    function it_logouts_the_user($tokenStorage, $session, $event)
    {
        $session->invalidate()->shouldBeCalled();
        $tokenStorage->setToken(null)->shouldBeCalled();

        $this->onChangePassword($event);
    }
}
