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

    function let(
        EngineInterface $templating,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        UserRepositoryInterface $userRepository,
        TokenGeneratorInterface $tokenGenerator,
        MailerInterface $mailer
    ) {
        $this->beConstructedWith(
            $templating,
            'template_path',
            $formFactory,
            $router,
            $userRepository,
            $tokenGenerator,
            $mailer
        );
    }

    function it_updates_confirmation_token_and_sends_mail(
        Request $request,
        FormFactoryInterface $formFactory,
        FormInterface $form,
        FormInterface $form2,
        UserRepositoryInterface $userRepository,
        UserPasswordResetInterface $user,
        TokenGeneratorInterface $tokenGenerator,
        MailerInterface $mailer,
        Session $session,
        FlashBagInterface $flashBag,
        RouterInterface $router
    ) {
        $formFactory->create('admin_password_reset_request')->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);

        $form->get('email')->willReturn($form2);
        $form2->getData()->willReturn('admin@fsi.pl');

        $userRepository->findUserByEmail('admin@fsi.pl')->willReturn($user);

        $tokenGenerator->generateToken()->willReturn('token1234');

        $user->setConfirmationToken('token1234')->shouldBeCalled();
        $user->setPasswordRequestedAt(Argument::type('\DateTime'))->shouldBeCalled();

        $userRepository->save($user)->shouldBeCalled();

        $mailer->sendPasswordResetMail($user)->shouldBeCalled();

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);

        $flashBag->add('password-reset.success', 'Reset password instructions sent')->shouldBeCalled();

        $router->generate('fsi_admin_security_password_reset_request')->willReturn('url');

        $response = $this->requestAction($request);
        $response->shouldHaveType('Symfony\Component\HttpFoundation\RedirectResponse');
    }
}
