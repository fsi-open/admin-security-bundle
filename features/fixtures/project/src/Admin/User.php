<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\FixturesBundle\Admin;

use FSi\Bundle\AdminSecurityBundle\Doctrine\Admin\UserElement;
use FSi\Component\DataGrid\DataGridFactoryInterface;
use FSi\Component\DataGrid\DataGridInterface;
use FSi\FixturesBundle\Entity\User as UserEntity;

/**
 * @template-extends UserElement<UserEntity, UserEntity>
 */
class User extends UserElement
{
    public function getId(): string
    {
        return 'user';
    }

    protected function initDataGrid(DataGridFactoryInterface $factory): DataGridInterface
    {
        return $factory->createDataGrid('admin_security_user');
    }
}
