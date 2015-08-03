<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Security\Token;

interface TokenInterface
{
    /**
     * @return string
     */
    public function getToken();

    /**
     * @return bool
     */
    public function isNonExpired();
}
