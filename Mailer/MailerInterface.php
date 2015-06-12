<?php

namespace FSi\Bundle\AdminSecurityBundle\Mailer;

use FOS\UserBundle\Model\UserInterface;

interface MailerInterface
{
    public function sendPasswordResetMail(UserInterface $user);
}
