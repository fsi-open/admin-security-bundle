<?php

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
