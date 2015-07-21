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
}
