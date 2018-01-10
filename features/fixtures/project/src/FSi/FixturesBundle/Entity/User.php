<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\FixturesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FSi\Bundle\AdminSecurityBundle\Security\User\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
}
