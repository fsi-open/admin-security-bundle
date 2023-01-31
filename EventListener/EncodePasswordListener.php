<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Event\UserCreatedEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

use function get_class;

class EncodePasswordListener implements EventSubscriberInterface
{
    private PasswordHasherFactoryInterface $passwordHasherFactory;

    public function __construct(PasswordHasherFactoryInterface $passwordHasherFactory)
    {
        $this->passwordHasherFactory = $passwordHasherFactory;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AdminSecurityEvents::CHANGE_PASSWORD => 'onChangePassword',
            AdminSecurityEvents::USER_CREATED => 'onUserCreated'
        ];
    }

    public function onChangePassword(ChangePasswordEvent $event): void
    {
        $this->updateUserPassword($event->getUser());
    }

    public function onUserCreated(UserCreatedEvent $event): void
    {
        $user = $event->getUser();
        if (true === $user instanceof ChangeablePasswordInterface) {
            $this->updateUserPassword($user);
        }
    }

    protected function updateUserPassword(ChangeablePasswordInterface $user): void
    {
        $password = $user->getPlainPassword();
        if (null === $password) {
            return;
        }

        if (false === $user instanceof UserInterface) {
            throw new RuntimeException(
                sprintf(
                    "Expected to get instance of %s but got instance of %s",
                    UserInterface::class,
                    get_class($user)
                )
            );
        }

        $user->setPassword(
            $this->passwordHasherFactory->getPasswordHasher($user)->hash($password)
        );

        $user->eraseCredentials();
    }
}
