<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\ExpressionLanguage\Token;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
        UserManagerInterface $userManager,
        TokenGeneratorInterface $tokenGenerator,
        MailerInterface $mailer
    )
    {
        $this->beConstructedWith(
            $templating,
            'template_path',
            $formFactory,
            $router,
            $userManager,
            $tokenGenerator,
            $mailer
        );
    }

    function it_updates_confirmation_token_and_sends_mail(
        Request $request,
        FormFactoryInterface $formFactory,
        FormInterface $form,
        FormInterface $form2,
        UserManagerInterface $userManager,
        UserInterface $user,
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

        $userManager->findUserByEmail('admin@fsi.pl')->willReturn($user);

        $tokenGenerator->generateToken()->willReturn('token1234');

        $user->setConfirmationToken('token1234')->shouldBeCalled();
        $user->setPasswordRequestedAt(Argument::type('\DateTime'))->shouldBeCalled();

        $userManager->updateUser($user)->shouldBeCalled();

        $mailer->sendPasswordResetMail($user)->shouldBeCalled();

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);

        $flashBag->add('password-reset.success', 'Reset password instructions sent')->shouldBeCalled();

        $router->generate('fsi_admin_security_password_reset_request')->willReturn('url');

        $response = $this->requestAction($request);
        $response->shouldHaveType('Symfony\Component\HttpFoundation\RedirectResponse');
    }
}
