<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Admin;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

interface SecuredElementInterface
{
    public function isAllowed(AuthorizationCheckerInterface $authorizationChecker): bool;
}
