<?php

namespace FSi\Bundle\AdminSecurityBundle\Mailer;

use FSi\Bundle\AdminSecurityBundle\Security\User\UserPasswordResetInterface;

interface MailerInterface
{
    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserPasswordResetInterface $user
     * @return int
     */
    public function sendPasswordResetMail(UserPasswordResetInterface $user);
}
