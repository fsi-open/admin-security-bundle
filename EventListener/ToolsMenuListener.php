<?php

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\MenuEvent;
use FSi\Bundle\AdminBundle\Menu\Item\Item;
use FSi\Bundle\AdminBundle\Menu\Item\RoutableItem;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ToolsMenuListener
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    public function __construct(
        TranslatorInterface $translator,
        SecurityContextInterface $securityContext
    ) {
        $this->translator = $translator;
        $this->securityContext = $securityContext;
    }

    public function createAccountMenu(MenuEvent $event)
    {
        $rootItem = $this->createRootItem();

        $changePasswordItem = new RoutableItem('account.change-password', 'fsi_admin_change_password');
        $changePasswordItem->setLabel($this->translator->trans('admin.change_password', array(), 'FSiAdminSecurity'));
        $rootItem->addChild($changePasswordItem);

        $logoutItem = new RoutableItem('account.logout', 'fsi_admin_security_user_logout');
        $logoutItem->setLabel($this->translator->trans('admin.logout', array(), 'FSiAdminSecurity'));
        $rootItem->addChild($logoutItem);

        $event->getMenu()->addChild($rootItem);

        return $event->getMenu();
    }

    /**
     * @return Item
     */
    private function createRootItem()
    {
        $rootItem = new Item('account');

        $rootItem->setLabel(
            $this->translator->trans(
                'admin.welcome',
                array('%username%' => $this->securityContext->getToken()->getUsername()),
                'FSiAdminSecurity'
            )
        );

        $rootItem->setOptions(array(
            'attr' => array(
                'id' => 'account',
            )
        ));

        return $rootItem;
    }
}
