<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Admin;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PasswordResetBatchElementSpec extends ObjectBehavior
{
    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface $tokenFactory
     * @param \FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface $mailer
     */
    function let($tokenFactory, $mailer)
    {
        $this->beConstructedWith(array(), 'FQCN\User\Model', $tokenFactory, $mailer);
    }

    function it_is_batch_element()
    {
        $this->shouldHaveType('FSi\Bundle\AdminBundle\Doctrine\Admin\BatchElement');
    }

    function it_should_return_class_name()
    {
        $this->getClassName()->shouldReturn('FQCN\User\Model');
    }

    function it_should_return_id()
    {
        $this->getId()->shouldReturn('admin_security_password_reset');
    }

    /**
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface $user
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenFactoryInterface $tokenFactory
     * @param \FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface $mailer
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface $token
     */
    function it_should_send_password_reset_email_to_selected_user($user, $tokenFactory, $mailer, $token)
    {
        $tokenFactory->createToken()->willReturn($token);
        $user->setPasswordResetToken($token)->shouldBeCalled();
        $mailer->send($user)->shouldBeCalled();

        $this->apply($user);
    }

    function it_should_handle_only_object_with_resettable_password_interface()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('apply', array(new \stdClass()));
    }
}
