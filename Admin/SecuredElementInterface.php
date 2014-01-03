<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Admin;

use Symfony\Component\Security\Core\SecurityContextInterface;

interface SecuredElementInterface
{
    /**
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @return Boolean
     */
    public function isAllowed(SecurityContextInterface $securityContext);
}