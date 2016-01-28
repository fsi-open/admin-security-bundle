<?php

namespace FSi\Bundle\AdminSecurityBundle\Mailer;

interface SwiftMessageFactoryInterface
{
    /**
     * @param string $email
     * @param string $template
     * @param array $data
     * @return \Swift_Message
     */
    public function createMessage($email, $template, array $data);
}
