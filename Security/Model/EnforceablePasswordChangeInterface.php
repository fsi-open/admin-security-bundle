<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Security\Model;

interface EnforceablePasswordChangeInterface extends ChangeablePasswordInterface
{
    /**
     * @return bool
     */
    public function isForcedToChangePassword();

    /**
     * @param bool $enforce
     */
    public function enforcePasswordChange($enforce);
}
