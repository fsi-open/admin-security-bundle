<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Event\UserCreatedEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

use function get_class;

class LegacyEncodePasswordListener implements EventSubscriberInterface
{
    private EncoderFactoryInterface $passwordEncoderFactory;

    public function __construct(EncoderFactoryInterface $passwordEncoderFactory)
    {
        $this->passwordEncoderFactory = $passwordEncoderFactory;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ChangePasswordEvent::class => 'onChangePassword',
            UserCreatedEvent::class => 'onUserCreated'
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
            $this->passwordEncoderFactory->getEncoder($user)->encodePassword($password, null)
        );

        $user->eraseCredentials();
    }
}
