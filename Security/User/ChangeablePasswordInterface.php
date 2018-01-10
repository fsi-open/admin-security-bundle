<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Security\User;

interface ChangeablePasswordInterface
{
    public function getPlainPassword(): ?string;

    public function setPassword(string $password): void;

    public function setPlainPassword(string $password): void;

    public function getSalt();

    public function eraseCredentials();
}
