<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset;

use FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface;
use FSi\Bundle\AdminSecurityBundle\Model\UserPasswordResetInterface;
use FSi\Bundle\AdminSecurityBundle\Model\UserRepositoryInterface;
use FSi\Bundle\AdminSecurityBundle\Token\TokenGeneratorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;

class ResetRequestControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset\ResetRequestController');
    }

    /**
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \FSi\Bundle\AdminSecurityBundle\Model\UserRepositoryInterface $userRepository
     * @param \FSi\Bundle\AdminSecurityBundle\Token\TokenGeneratorInterface $tokenGenerator
     * @param \FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface $mailer
     */
    function let($templating, $formFactory, $router, $userRepository, $tokenGenerator, $mailer)
    {
        $this->beConstructedWith(
            $templating,
            'template_path',
            $formFactory,
            $router,
            $userRepository,
            $tokenGenerator,
            $mailer,
            3600 * 12
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\Form\FormInterface $form2
     * @param \FSi\Bundle\AdminSecurityBundle\Model\UserRepositoryInterface $userRepository
     * @param \FSi\Bundle\AdminSecurityBundle\Model\UserPasswordResetInterface $user
     * @param \FSi\Bundle\AdminSecurityBundle\Token\TokenGeneratorInterface $tokenGenerator
     * @param \FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface $mailer
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     * @param \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $flashBag
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    function it_updates_confirmation_token_and_sends_mail(
        $request,
        $formFactory,
        $form,
        $form2,
        $userRepository,
        $user,
        $tokenGenerator,
        $mailer,
        $session,
        $flashBag,
        $router
    ) {
        $formFactory->create('admin_password_reset_request')->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);

        $form->get('email')->willReturn($form2);
        $form2->getData()->willReturn('admin@fsi.pl');

        $userRepository->findUserByEmail('admin@fsi.pl')->willReturn($user);

        $user->isPasswordRequestNonExpired(3600 * 12)->willReturn(false);

        $tokenGenerator->generateToken()->willReturn('token1234');

        $user->setConfirmationToken('token1234')->shouldBeCalled();
        $user->setPasswordRequestedAt(Argument::type('\DateTime'))->shouldBeCalled();

        $userRepository->save($user)->shouldBeCalled();

        $mailer->sendPasswordResetMail($user)->shouldBeCalled();

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);

        $flashBag->add('alert-success', 'admin.password_reset.request.mail_sent')->shouldBeCalled();

        $router->generate('fsi_admin_security_password_reset_request')->willReturn('url');

        $response = $this->requestAction($request);
        $response->shouldHaveType('Symfony\Component\HttpFoundation\RedirectResponse');
    }
}
