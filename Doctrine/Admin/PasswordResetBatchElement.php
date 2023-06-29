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
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @extends BatchElement<UserInterface>
 */
class PasswordResetBatchElement extends BatchElement
{
    private EventDispatcherInterface $eventDispatcher;
    /**
     * @var class-string<UserInterface>
     */
    private string $userModel;

    /**
     * @param array<string, mixed> $options
     * @param class-string<UserInterface> $userModel
     */
    public function __construct(array $options, string $userModel, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($options);

        $this->userModel = $userModel;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function apply($object): void
    {
        $this->eventDispatcher->dispatch(new ResetPasswordRequestEvent($object));
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
