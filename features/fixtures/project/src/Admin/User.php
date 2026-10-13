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
        $dataGrid = $factory->createDataGrid($this->getId());

        $dataGrid->addColumn('batch', 'batch', [
            'display_order' => 1,
            'translation_domain' => 'FSiAdminSecurity',
            'actions' => [
                'delete' => [
                    'label' => 'admin.user_list.batch_action.delete',
                    'route_name' => 'fsi_admin_batch',
                    'additional_parameters' => ['element' => $this->getId()]
                ],
                'password_reset' => [
                    'label' => 'admin.user_list.batch_action.password_reset',
                    'element' => 'admin_security_password_reset'
                ],
                'resend_activation_token' => [
                    'label' => 'admin.user_list.batch_action.resend_activation_token',
                    'element' => 'admin_security_reset_activation_token'
                ],
            ]
        ]);

        $dataGrid->addColumn('email', 'text', [
            'display_order' => 2,
            'label' => 'admin.user_list.datagrid.email',
            'translation_domain' => 'FSiAdminSecurity',
        ]);

        $dataGrid->addColumn('enabled', 'boolean', [
            'display_order' => 3,
            'label' => 'admin.user_list.datagrid.enabled',
            'translation_domain' => 'FSiAdminSecurity',
        ]);

        $dataGrid->addColumn('last_login', 'datetime', [
            'display_order' => 4,
            'label' => 'admin.user_list.datagrid.last_login',
            'translation_domain' => 'FSiAdminSecurity',
        ]);

        $dataGrid->addColumn('password_reset_token_non_expired', 'token_non_expired', [
            'display_order' => 5,
            'label' => 'admin.user_list.datagrid.password_reset_token',
            'translation_domain' => 'FSiAdminSecurity',
            'field_mapping' => ['passwordResetToken'],
        ]);

        $dataGrid->addColumn('activation_token_non_expired', 'token_non_expired', [
            'display_order' => 6,
            'label' => 'admin.user_list.datagrid.activation_token',
            'translation_domain' => 'FSiAdminSecurity',
            'field_mapping' => ['activationToken'],
        ]);

        $dataGrid->addColumn('actions', 'action', [
            'display_order' => 7,
            'field_mapping' => ['id'],
            'actions' => [
                'Edit' => [
                    'route_name' => 'fsi_admin_form',
                    'additional_parameters' => ['element' => $this->getId()],
                    'parameters_field_mapping' => [ 'id' => 'id' ],
                ]
            ]
        ]);

        return $dataGrid;
    }
}
