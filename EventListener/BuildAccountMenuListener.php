<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\MenuEvent;
use FSi\Bundle\AdminBundle\Menu\Item\Item;
use FSi\Bundle\AdminBundle\Menu\Item\RoutableItem;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BuildAccountMenuListener
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
    }

    public function createAccountMenu(MenuEvent $event)
    {
        if (!$this->hasUserLoggedIn()) {
            return null;
        }

        $rootItem = $this->createRootItem();

        if ($this->canChangeUserPassword()) {
            $changePasswordItem = new RoutableItem('account.change-password', 'fsi_admin_change_password');
            $changePasswordItem->setLabel($this->translator->trans('admin.change_password', array(), 'FSiAdminSecurity'));
            $rootItem->addChild($changePasswordItem);
        }

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
                array('%username%' => $this->tokenStorage->getToken()->getUsername()),
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

    /**
     * @return bool
     */
    private function hasUserLoggedIn()
    {
        return $this->tokenStorage->getToken();
    }

    /**
     * @return bool
     */
    private function canChangeUserPassword()
    {
        return $this->tokenStorage->getToken()->getUser() instanceof ChangeablePasswordInterface;
    }
}
