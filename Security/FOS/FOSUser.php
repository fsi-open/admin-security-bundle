<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Security\FOS;

use FOS\UserBundle\Entity\User as BaseUser;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;

abstract class FOSUser extends BaseUser implements UserInterface
{
    /**
     * @var boolean
     */
    protected $enforcePasswordChange;

    /**
     * @var TokenInterface
     */
    protected $activationToken;

    /**
     * @var TokenInterface
     */
    protected $passwordResetToken;

    public function __construct()
    {
        parent::__construct();

        $this->enforcePasswordChange = false;
    }

    /**
     * @return bool
     */
    public function isForcedToChangePassword()
    {
        return $this->enforcePasswordChange;
    }

    /**
     * @param bool $enforcePasswordChange
     */
    public function enforcePasswordChange($enforcePasswordChange)
    {
        $this->enforcePasswordChange = $enforcePasswordChange;
    }

    /**
     * @return TokenInterface
     */
    public function getActivationToken()
    {
        return $this->activationToken;
    }

    /**
     * @param TokenInterface $confirmationToken
     */
    public function setActivationToken(TokenInterface $confirmationToken)
    {
        $this->activationToken = $confirmationToken;
    }

    public function removeActivationToken()
    {
        $this->activationToken = null;
    }

    /**
     * @return TokenInterface
     */
    public function getPasswordResetToken()
    {
        return $this->passwordResetToken;
    }

    /**
     * @param TokenInterface $passwordResetToken
     */
    public function setPasswordResetToken(TokenInterface $passwordResetToken)
    {
        $this->passwordResetToken = $passwordResetToken;
    }

    public function removePasswordResetToken()
    {
        $this->passwordResetToken = null;
    }
}
