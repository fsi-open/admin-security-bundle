<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Event;

class AdminSecurityEvents
{
    public const CHANGE_PASSWORD = 'admin.security.change_password';
    public const RESET_PASSWORD_REQUEST = 'admin.security.reset_password_request';
    public const ACTIVATION = 'admin.security.activation';
    public const DEACTIVATION = 'admin.security.deactivation';
    public const USER_CREATED = 'admin.security.user_created';
    public const PROMOTE_USER = 'admin.security.promote_user';
    public const DEMOTE_USER = 'admin.security.demote_user';
}
