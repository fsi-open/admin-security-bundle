<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\MenuEvent;
use FSi\Bundle\AdminBundle\Menu\Item\Item;
use FSi\Bundle\AdminBundle\Menu\Item\RoutableItem;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    public function __construct(TranslatorInterface $translator, TokenStorageInterface $tokenStorage)
    {
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
    }

    public function createAccountMenu(MenuEvent $event): Item
    {
        if (false === $this->hasUserLoggedIn()) {
            return $event->getMenu();
        }

        $rootItem = $this->createRootItem();

        if (true === $this->canChangeUserPassword()) {
            $changePasswordItem = new RoutableItem('account.change-password', 'fsi_admin_change_password');
            $changePasswordItem->setLabel($this->translator->trans('admin.change_password', [], 'FSiAdminSecurity'));
            $rootItem->addChild($changePasswordItem);
        }

        $logoutItem = new RoutableItem('account.logout', 'fsi_admin_security_user_logout');
        $logoutItem->setLabel($this->translator->trans('admin.logout', [], 'FSiAdminSecurity'));
        $rootItem->addChild($logoutItem);

        $event->getMenu()->addChild($rootItem);

        return $event->getMenu();
    }

    private function createRootItem(): Item
    {
        $rootItem = new Item('account');

        $rootItem->setLabel(
            $this->translator->trans(
                'admin.welcome',
                ['%username%' => $this->tokenStorage->getToken()->getUsername()],
                'FSiAdminSecurity'
            )
        );

        $rootItem->setOptions([
            'attr' => [
                'id' => 'account',
            ]
        ]);

        return $rootItem;
    }

    private function hasUserLoggedIn(): bool
    {
        return $this->tokenStorage->getToken() !== null;
    }

    private function canChangeUserPassword(): bool
    {
        return $this->tokenStorage->getToken()->getUser() instanceof ChangeablePasswordInterface;
    }
}
