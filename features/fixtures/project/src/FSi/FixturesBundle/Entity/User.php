<?php

namespace FSi\FixturesBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use FSi\Bundle\AdminSecurityBundle\Model\UserPasswordResetInterface;

/**
 * @ORM\Entity(repositoryClass="\FSi\Bundle\AdminSecurityBundle\Doctrine\UserRepository")
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser implements UserPasswordResetInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}
