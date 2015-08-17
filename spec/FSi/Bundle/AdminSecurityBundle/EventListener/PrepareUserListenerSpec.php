<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PrepareUserListenerSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Security\Core\Util\SecureRandomInterface $secureRandom
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface $tokenFactory
     * @param \FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface $mailer
     * @param \FSi\Bundle\AdminBundle\Event\FormEvent $event
     * @param \Symfony\Component\Form\FormInterface $form
     */
    function let($tokenStorage, $secureRandom, $encoderFactory, $tokenFactory, $mailer, $event, $form)
    {
        $event->getForm()->willReturn($form);

        $this->beConstructedWith($tokenStorage, $secureRandom, $encoderFactory, $tokenFactory, $mailer);
    }

    function it_is_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    /**
     * @param \FSi\Bundle\AdminBundle\Event\FormEvent $event
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     */
    function it_should_set_email_as_username_and_enable_user($event, $form, $user)
    {
        $form->getData()->willReturn($user);

        $user->getEmail()->willReturn('test@example.com');
        $user->setUsername('test@example.com')->shouldBeCalled();
        $user->setEnabled(true)->shouldBeCalled();

        $this->setEmailAsUsernameAndEnableUser($event);
    }

    /**
     * @param \FSi\Bundle\AdminBundle\Event\FormEvent $event
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface $user
     * @param \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface $passwordEncoder
     * @param \Symfony\Component\Security\Core\Util\SecureRandomInterface $secureRandom
     */
    function it_should_set_random_password($event, $form, $encoderFactory, $user, $passwordEncoder, $secureRandom)
    {
        $form->getData()->willReturn($user);

        $encoderFactory->getEncoder($user)->willReturn($passwordEncoder);
        $secureRandom->nextBytes(32)->willReturn('12345678901234567890123456789012');
        $user->getSalt()->willReturn('salt1234');

        $passwordEncoder->encodePassword('12345678901234567890123456789012', 'salt1234')->willReturn('hashed-password');

        $user->setPassword('hashed-password')->shouldBeCalled();
        $user->eraseCredentials()->shouldBeCalled();

        $this->setRandomPassword($event);
    }

    /**
     * @param \FSi\Bundle\AdminBundle\Event\FormEvent $event
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface $user
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface $tokenFactory
     * @param \FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface $mailer
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface $token
     */
    function it_should_send_password_reset_email($event, $form, $user, $tokenFactory, $mailer, $token)
    {
        $form->getData()->willReturn($user);

        $tokenFactory->createToken()->willReturn($token);

        $user->setPasswordResetToken($token)->shouldBeCalled();
        $mailer->send($user)->shouldBeCalled();

        $this->sendPasswordResetEmail($event);
    }
}
