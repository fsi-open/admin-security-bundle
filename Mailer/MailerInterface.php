<?php

namespace FSi\Bundle\AdminSecurityBundle\Mailer;

use FSi\Bundle\AdminSecurityBundle\Model\UserPasswordResetInterface;

interface MailerInterface
{
    public function sendPasswordResetMail(UserPasswordResetInterface $user);
}
