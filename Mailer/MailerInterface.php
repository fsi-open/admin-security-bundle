<?php

namespace FSi\Bundle\AdminSecurityBundle\Mailer;

interface MailerInterface
{
    /**
     * @param EmailableInterface $to
     * @return int
     */
    public function send(EmailableInterface $to);
}
