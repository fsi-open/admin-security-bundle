<?php

namespace FSi\FixturesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FSi\Bundle\AdminSecurityBundle\Entity\User as BaseUser;

/**
 * @ORM\Entity(repositoryClass="FSi\Bundle\AdminSecurityBundle\Doctrine\UserRepository")
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
}
