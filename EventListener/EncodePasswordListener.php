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
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

use function get_class;

class EncodePasswordListener implements EventSubscriberInterface
{
    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

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

    public function onUserCreated(UserEvent $event)
    {
        $user = $event->getUser();
        if (true === $user instanceof ChangeablePasswordInterface) {
            $this->updateUserPassword($user);
        }
    }

    protected function updateUserPassword(ChangeablePasswordInterface $user): void
    {
        $password = $user->getPlainPassword();
        if (null !== $password) {
            if (false === $user instanceof UserInterface) {
                throw new RuntimeException(
                    sprintf(
                        "Expected to get instance of %s but got instance of %s",
                        UserInterface::class,
                        get_class($user)
                    )
                );
            }
            $encoder = $this->getEncoder($user);
            $user->setPassword($encoder->encodePassword($password, $user->getSalt()));
            $user->eraseCredentials();
        }
    }

    protected function getEncoder(UserInterface $user): PasswordEncoderInterface
    {
        return $this->encoderFactory->getEncoder($user);
    }
}
