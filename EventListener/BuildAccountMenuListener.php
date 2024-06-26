<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\MenuToolsEvent;
use FSi\Bundle\AdminBundle\Menu\Item\Item;
use FSi\Bundle\AdminBundle\Menu\Item\RoutableItem;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserIdentifierHelper;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BuildAccountMenuListener
{
    private TranslatorInterface $translator;
    private TokenStorageInterface $tokenStorage;

    public function __construct(TranslatorInterface $translator, TokenStorageInterface $tokenStorage)
    {
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
    }

    public function createAccountMenu(MenuToolsEvent $event): Item
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

        $menu = $event->getMenu();
        $menu->addChild($rootItem);

        return $menu;
    }

    private function createRootItem(): Item
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            throw new RuntimeException('No logged in user.');
        }

        $rootItem = new Item('account');
        $rootItem->setLabel(
            $this->translator->trans(
                'admin.welcome',
                ['%username%' => UserIdentifierHelper::getTokenUserIdentifier($token)],
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
        return null !== $this->tokenStorage->getToken();
    }

    private function canChangeUserPassword(): bool
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            throw new RuntimeException('No logged in user.');
        }

        return $token->getUser() instanceof ChangeablePasswordInterface;
    }
}
