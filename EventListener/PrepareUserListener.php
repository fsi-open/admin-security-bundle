<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\FormEvent;
use FSi\Bundle\AdminBundle\Event\FormEvents;
use FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Util\SecureRandomInterface;

class PrepareUserListener implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var SecureRandomInterface
     */
    private $secureRandom;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var TokenFactoryInterface
     */
    private $tokenFactory;

    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        SecureRandomInterface $secureRandom,
        EncoderFactoryInterface $encoderFactory,
        TokenFactoryInterface $tokenFactory,
        MailerInterface $mailer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->secureRandom = $secureRandom;
        $this->encoderFactory = $encoderFactory;
        $this->tokenFactory = $tokenFactory;
        $this->mailer = $mailer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::FORM_DATA_PRE_SAVE => array(
                array('setEmailAsUsernameAndEnableUser', 1),
                array('setRandomPassword', 2),
                array('sendPasswordResetEmail', 3),
            )
        );
    }

    public function setEmailAsUsernameAndEnableUser(FormEvent $event)
    {
        $entity = $event->getForm()->getData();

        if (!$entity instanceof UserInterface) {
            return;
        }

        $entity->setUsername($entity->getEmail());
        $entity->setEnabled(true);

//        if ($entity->getUsername() === $this->tokenStorage->getToken()->getUsername()) {
//            // you cannot delete yourself
//            return;
//        }
    }

    public function setRandomPassword(FormEvent $event)
    {
        $entity = $event->getForm()->getData();

        if (!$entity instanceof ChangeablePasswordInterface) {
            return;
        }

        $encoder = $this->encoderFactory->getEncoder($entity);
        $hashedPassword = $encoder->encodePassword($this->secureRandom->nextBytes(32), $entity->getSalt());
        $entity->setPassword($hashedPassword);
        $entity->eraseCredentials();
    }

    public function sendPasswordResetEmail(FormEvent $event)
    {
        $entity = $event->getForm()->getData();

        if (!$entity instanceof ResettablePasswordInterface) {
            return;
        }

        $entity->setPasswordResetToken($this->tokenFactory->createToken());
        $this->mailer->send($entity);
    }
}
