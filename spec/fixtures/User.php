<?php

namespace FSi\Bundle\AdminSecurityBundle\spec\fixtures;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    private $password;

    public function getRoles()
    {
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
    }

    public function eraseCredentials()
    {
    }
}
