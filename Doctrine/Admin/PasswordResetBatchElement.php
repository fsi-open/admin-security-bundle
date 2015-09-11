<?php

namespace FSi\Bundle\AdminSecurityBundle\Doctrine\Admin;

use FSi\Bundle\AdminBundle\Doctrine\Admin\BatchElement;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PasswordResetBatchElement extends BatchElement
{
    /**
     * @var string
     */
    private $userModel;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct($options, $userModel, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($options);
        $this->userModel = $userModel;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ResettablePasswordInterface $object
     */
    public function apply($object)
    {
        $this->eventDispatcher->dispatch(
            AdminSecurityEvents::RESET_PASSWORD_REQUEST,
            new ResetPasswordRequestEvent($object)
        );
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
