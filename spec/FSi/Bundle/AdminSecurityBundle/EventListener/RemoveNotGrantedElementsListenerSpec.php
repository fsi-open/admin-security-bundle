<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Admin\Element;
use FSi\Bundle\AdminBundle\Admin\ManagerInterface;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\SecuredElement;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RemoveNotGrantedElementsListenerSpec extends ObjectBehavior
{
    function let(
        ManagerInterface $adminManager,
        AuthorizationCheckerInterface $authorizationChecker,
        GetResponseEvent $event
    ) {
        $event->getRequestType()->willReturn(Kernel::MASTER_REQUEST);

        $this->beConstructedWith($adminManager, $authorizationChecker);
    }

    function it_do_nothing_when_request_is_not_master_request(
        ManagerInterface $adminManager,
        GetResponseEvent $event
    ) {
        $event->getRequestType()->shouldBeCalled()->willReturn(Kernel::SUB_REQUEST);
        $adminManager->getElements()->shouldNotBeCalled();
        $this->onKernelRequest($event);
    }

    function it_remove_element_from_admin_manager_when_its_secured_from_user(
        ManagerInterface $adminManager,
        AuthorizationCheckerInterface $authorizationChecker,
        GetResponseEvent $event,
        SecuredElement $securedElement,
        Element $element
    ) {
        $adminManager->getElements()->shouldBeCalled()->willReturn([
            $securedElement,
            $element
        ]);

        $securedElement->isAllowed($authorizationChecker)->willReturn(false);

        $securedElement->getId()->shouldBeCalled()->willReturn('secured');

        $adminManager->removeElement('secured')->shouldBeCalled();

        $this->onKernelRequest($event);
    }
}
