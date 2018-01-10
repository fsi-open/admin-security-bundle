<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

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
    public function apply($object): void
    {
        $this->eventDispatcher->dispatch(
            AdminSecurityEvents::RESET_PASSWORD_REQUEST,
            new ResetPasswordRequestEvent($object)
        );
    }

    public function getId(): string
    {
        return 'admin_security_password_reset';
    }

    public function getClassName(): string
    {
        return $this->userModel;
    }
}
