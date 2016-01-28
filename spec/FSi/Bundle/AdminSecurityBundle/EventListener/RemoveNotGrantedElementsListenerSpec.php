<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Admin\Manager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Kernel;

class RemoveNotGrantedElementsListenerSpec extends ObjectBehavior
{
    /**
     * @param \FSi\Bundle\AdminBundle\Admin\Manager $adminManager
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    function let($adminManager, $authorizationChecker, $event)
    {
        $event->getRequestType()->willReturn(Kernel::MASTER_REQUEST);

        $this->beConstructedWith($adminManager, $authorizationChecker);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @param \FSi\Bundle\AdminBundle\Admin\Manager $adminManager
     */
    function it_do_nothing_when_request_is_not_master_request($event, $adminManager)
    {
        $event->getRequestType()->shouldBeCalled()->willReturn(Kernel::SUB_REQUEST);
        $adminManager->getElements()->shouldNotBeCalled();
        $this->onKernelRequest($event);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @param \FSi\Bundle\AdminBundle\Admin\Manager $adminManager
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \FSi\Bundle\AdminSecurityBundle\spec\fixtures\SecuredElement $securedElement
     * @param \FSi\Bundle\AdminBundle\Admin\Element $element
     */
    function it_remove_element_from_admin_manager_when_its_secured_from_user(
        $event, $adminManager, $authorizationChecker, $securedElement, $element
    ) {
        $adminManager->getElements()->shouldBeCalled()->willReturn(array(
            $securedElement,
            $element
        ));

        $securedElement->isAllowed($authorizationChecker)->willReturn(false);

        $securedElement->getId()->shouldBeCalled()->willReturn('secured');

        $adminManager->removeElement('secured')->shouldBeCalled();

        $this->onKernelRequest($event);
    }
}
