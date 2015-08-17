<?php

namespace FSi\Bundle\AdminSecurityBundle\Admin;

use FSi\Bundle\AdminBundle\Doctrine\Admin\BatchElement;
use FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;

class PasswordResetBatchElement extends BatchElement
{
    /**
     * @var string
     */
    private $userModel;

    /**
     * @var TokenFactoryInterface
     */
    private $tokenFactory;

    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct($options, $userModel, TokenFactoryInterface $tokenFactory, MailerInterface $mailer)
    {
        parent::__construct($options);
        $this->userModel = $userModel;
        $this->tokenFactory = $tokenFactory;
        $this->mailer = $mailer;
    }

    /**
     * @param ResettablePasswordInterface $object
     */
    public function apply($object)
    {
        if (!$object instanceof ResettablePasswordInterface) {
            throw new \InvalidArgumentException(
                'Object should implement FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface'
            );
        }

        $object->setPasswordResetToken($this->tokenFactory->createToken());
        $this->mailer->send($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'admin_security_password_reset';
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return $this->userModel;
    }
}
