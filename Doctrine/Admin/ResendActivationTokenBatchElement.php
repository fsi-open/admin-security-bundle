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
use FSi\Bundle\AdminSecurityBundle\Event\ResendActivationTokenEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

/**
 * @extends BatchElement<UserInterface>
 */
final class ResendActivationTokenBatchElement extends BatchElement
{
    private EventDispatcherInterface $eventDispatcher;
    /**
     * @var class-string<UserInterface>
     */
    private string $userModel;

    /**
     * @param class-string<UserInterface> $userModel
     * @param array<string, mixed> $options
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, string $userModel, array $options)
    {
        parent::__construct($options);

        $this->userModel = $userModel;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getId(): string
    {
        return 'admin_security_reset_activation_token';
    }

    public function getClassName(): string
    {
        return $this->userModel;
    }

    /**
     * @param ActivableInterface $object
     */
    public function apply($object): void
    {
        if (false === $object instanceof ActivableInterface) {
            throw new RuntimeException(sprintf(
                'Expected an instance of "%s", got "%s" instead.',
                ActivableInterface::class,
                true === is_object($object) ? get_class($object) : gettype($object)
            ));
        }

        if (true === $object->isEnabled()) {
            return;
        }

        $this->eventDispatcher->dispatch(new ResendActivationTokenEvent($object));
    }
}
