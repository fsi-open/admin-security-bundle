<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Event;

class AdminSecurityEvents
{
    const CHANGE_PASSWORD = 'admin.security.change_password';
    const RESET_PASSWORD_REQUEST = 'admin.security.reset_password_request';
    const ACTIVATION = 'admin.security.activation';
    const DEACTIVATION = 'admin.security.deactivation';
    const USER_CREATED = 'admin.security.user_created';
    const PROMOTE_USER = 'admin.security.promote_user';
    const DEMOTE_USER = 'admin.security.demote_user';
}
