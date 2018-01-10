<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Security\User;

use FSi\Bundle\AdminSecurityBundle\Mailer\EmailableInterface;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;

interface ActivableInterface extends EmailableInterface
{
    public function isEnabled();

    public function setEnabled(bool $boolean): void;

    public function getActivationToken(): ?TokenInterface;

    public function setActivationToken(TokenInterface $token): void;

    public function removeActivationToken(): void;
}
