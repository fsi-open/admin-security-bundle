<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Security\Model;

use FSi\Bundle\AdminSecurityBundle\Mailer\EmailableInterface;

interface ActivableInterface extends EmailableInterface
{
    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @param boolean $boolean
     */
    public function setEnabled($boolean);

    /**
     * @return TokenInterface
     */
    public function getActivationToken();

    /**
     * @param TokenInterface $token
     */
    public function setActivationToken(TokenInterface $token);

    public function removeActivationToken();
}
