<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\MenuEvent;
use FSi\Bundle\AdminBundle\Menu\Item\Item;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ToolsMenuListenerSpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator, SecurityContextInterface $securityContext, TokenInterface $token)
    {
        $securityContext->getToken()->willReturn($token);
        $token->getUsername()->willReturn('some user');

        $translator->trans('admin.welcome', array('%username%' => 'some user'), 'FSiAdminSecurity')
            ->willReturn('Hello some user');
        $translator->trans('admin.change_password', array(), 'FSiAdminSecurity')->willReturn('change password');
        $translator->trans('admin.logout', array(), 'FSiAdminSecurity')->willReturn('logout');

        $this->beConstructedWith($translator, $securityContext);
    }

    function it_builds_account_menu()
    {
        $this->createAccountMenu(new MenuEvent(new Item()))->shouldHaveItem('account', false);
        $this->createAccountMenu(new MenuEvent(new Item()))->shouldHaveItemThatHaveChild('account', 'account.change-password', 'fsi_admin_change_password');
        $this->createAccountMenu(new MenuEvent(new Item()))->shouldHaveItemThatHaveChild('account', 'account.logout', 'fsi_admin_security_user_logout');
    }

    public function getMatchers()
    {
        return array(
            'haveItem' => function(Item $menu, $itemName, $route = false) {
                $items = $menu->getChildren();
                foreach ($items as $item) {
                    if ($item->getName() === $itemName) {
                        if (!$route) {
                            return true;
                        }
                        /** @var ElementItem $item */
                        return $item->getRoute() === $route;
                    }
                }
                return false;
            },
            'haveItemThatHaveChild' => function(Item $menu, $itemName, $childName, $route = false) {
                foreach ($menu->getChildren() as $item) {
                    if ($item->getName() === $itemName && $item->hasChildren()) {
                        foreach ($item->getChildren() as $child) {
                            if ($child->getName() === $childName) {
                                if (!$route) {
                                    return true;
                                }
                                /** @var ElementItem $child */
                                return $child->getRoute() === $route;
                            }
                        }
                    }
                }
                return false;
            }
        );
    }
}
