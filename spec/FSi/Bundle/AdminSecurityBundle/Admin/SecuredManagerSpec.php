<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\FSi\Bundle\AdminSecurityBundle\Admin;

use FSi\Bundle\AdminBundle\Admin\Element;
use FSi\Bundle\AdminBundle\Admin\ManagerInterface;
use FSi\Bundle\AdminBundle\Admin\Manager\Visitor;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\SecuredElement;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecuredManagerSpec extends ObjectBehavior
{
    const INSECURE_ID = 'insecure';
    const SECURE_ID = 'secure';

    function let(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        ManagerInterface $manager,
        Element $insecureElement,
        SecuredElement $securedElement
    ) {
        $insecureElement->getId()->willReturn(self::INSECURE_ID);
        $securedElement->getId()->willReturn(self::SECURE_ID);
        $manager->hasElement(self::INSECURE_ID)->willReturn(true);
        $manager->hasElement(self::SECURE_ID)->willReturn(true);
        $manager->getElement(self::INSECURE_ID)->willReturn($insecureElement);
        $manager->getElement(self::SECURE_ID)->willReturn($securedElement);
        $tokenStorage->getToken()->willReturn($token);

        $this->beConstructedWith($manager, $tokenStorage, $authorizationChecker);
    }

    function it_implements_manager_interface()
    {
        $this->beAnInstanceOf('\FSi\Bundle\AdminBundle\Admin\ManagerInterface');
    }

    function it_adds_element_regardless_of_access(
        ManagerInterface $manager,
        AuthorizationCheckerInterface $authorizationChecker,
        Element $insecureElement,
        SecuredElement $securedElement
    ) {
        $manager->addElement($insecureElement)->shouldBeCalled();
        $this->addElement($insecureElement);

        $securedElement->isAllowed($authorizationChecker)->willReturn(false);
        $manager->addElement($securedElement)->shouldBeCalled();
        $this->addElement($securedElement);

        $securedElement->isAllowed($authorizationChecker)->willReturn(true);
        $manager->addElement($securedElement)->shouldBeCalled();
        $this->addElement($securedElement);
    }

    function it_removes_element_by_id_regardless_of_access(
        ManagerInterface $manager,
        AuthorizationCheckerInterface $authorizationChecker,
        SecuredElement $securedElement
    ) {
        $manager->removeElement(self::INSECURE_ID)->shouldBeCalled();
        $this->removeElement(self::INSECURE_ID);

        $securedElement->isAllowed($authorizationChecker)->willReturn(false);
        $manager->removeElement(self::SECURE_ID)->shouldBeCalled();
        $this->removeElement(self::SECURE_ID);

        $securedElement->isAllowed($authorizationChecker)->willReturn(true);
        $manager->removeElement(self::SECURE_ID)->shouldBeCalled();
        $this->removeElement(self::SECURE_ID);
    }

    function it_returns_true_for_has_element_if_access_allowed(
        AuthorizationCheckerInterface $authorizationChecker,
        SecuredElement $securedElement
    ) {
        $this->hasElement(self::INSECURE_ID)->shouldReturn(true);

        $securedElement->isAllowed($authorizationChecker)->willReturn(true);
        $this->hasElement(self::SECURE_ID)->shouldReturn(true);
    }

    function it_returns_false_for_has_element_if_access_restricted(
        AuthorizationCheckerInterface $authorizationChecker,
        SecuredElement $securedElement
    ) {
        $this->hasElement(self::INSECURE_ID)->shouldReturn(true);

        $securedElement->isAllowed($authorizationChecker)->willReturn(false);
        $this->hasElement(self::SECURE_ID)->shouldReturn(false);
    }

    function it_throws_exception_when_trying_to_get_a_restricted_element(
        AuthorizationCheckerInterface $authorizationChecker,
        SecuredElement $securedElement
    ) {
        $securedElement->isAllowed($authorizationChecker)->willReturn(false);

        $this->shouldThrow(
            '\Symfony\Component\Security\Core\Exception\AccessDeniedException'
        )->during('getElement', [self::SECURE_ID]);
    }

    function it_returns_only_allowed_elements(
        ManagerInterface $manager,
        AuthorizationCheckerInterface $authorizationChecker,
        Element $insecureElement,
        SecuredElement $securedElement
    ) {
        $manager->getElements()->willReturn([$insecureElement, $securedElement]);

        $securedElement->isAllowed($authorizationChecker)->willReturn(false);
        $this->getElements()->shouldReturn([$insecureElement]);

        $securedElement->isAllowed($authorizationChecker)->willReturn(true);
        $this->getElements()->shouldReturn([$insecureElement, $securedElement]);
    }

    function it_returns_no_elements_when_not_behind_a_firewall(
        ManagerInterface $manager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        Element $insecureElement,
        SecuredElement $securedElement

    ){
        $manager->getElements()->willReturn([$insecureElement, $securedElement]);
        $tokenStorage->getToken()->willReturn(null);
        $securedElement->isAllowed($authorizationChecker)->shouldNotBeCalled();

        $this->getElements()->shouldReturn([]);
    }

    function it_accepts_visitors(Visitor $visitor)
    {
        $visitor->visitManager($this)->shouldBeCalled();
        $this->accept($visitor);
    }
}
