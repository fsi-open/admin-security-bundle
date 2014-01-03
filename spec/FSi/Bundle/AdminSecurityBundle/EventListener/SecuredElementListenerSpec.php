<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Admin\ElementInterface;
use FSi\Bundle\AdminBundle\Admin\Manager;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\SecuredElement;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;

class SecuredElementListenerSpec extends ObjectBehavior
{
    function let(
        Manager $adminManager,
        SecurityContext $securityContext,
        UsernamePasswordToken $token,
        GetResponseEvent $event
    ) {
        $securityContext->getToken()->willReturn($token);
        $event->getRequestType()->willReturn(Kernel::MASTER_REQUEST);

        $this->beConstructedWith($adminManager, $securityContext);
    }

    function it_do_nothing_when_request_is_not_master_request(
        GetResponseEvent $event,
        Manager $adminManager
    ) {
        $event->getRequestType()->shouldBeCalled()->willReturn(Kernel::SUB_REQUEST);
        $adminManager->getElements()->shouldNotBeCalled();
        $this->onKernelRequest($event);
    }

    function it_do_nothing_when_request_is_made_without_user_token_in_security_context(
        SecurityContext $securityContext,
        GetResponseEvent $event,
        Manager $adminManager
    ) {
        $securityContext->getToken()->willReturn(null);
        $adminManager->getElements()->shouldNotBeCalled();
        $this->onKernelRequest($event);
    }

    function it_remove_element_from_admin_manager_when_its_secured_from_user(
        GetResponseEvent $event,
        Manager $adminManager,
        SecuredElement $securedElement,
        ElementInterface $element
    ) {
        $adminManager->getElements()->shouldBeCalled()->willReturn(array(
            $securedElement,
            $element
        ));

        $securedElement->isAllowed(
            Argument::type('Symfony\Component\Security\Core\SecurityContext')
        )->shouldBeCalled()
            ->willReturn(false);

        $securedElement->getId()->shouldBeCalled()->willReturn('secured');

        $adminManager->removeElement('secured')->shouldBeCalled();

        $this->onKernelRequest($event);
    }
}
