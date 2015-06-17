<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset;

use FSi\Bundle\AdminSecurityBundle\Model\UserPasswordResetInterface;
use FSi\Bundle\AdminSecurityBundle\Model\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;

class ChangePasswordControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset\ChangePasswordController');
    }

    function let(
        EngineInterface $templating,
        UserRepositoryInterface $userRepository,
        RouterInterface $router,
        FormFactoryInterface $formFactory
    ) {
        $this->beConstructedWith($templating, 'template-name', $userRepository, $router, $formFactory);
    }

    function it_changes_password(
        Request $request,
        UserRepositoryInterface $userRepository,
        UserPasswordResetInterface $user,
        FormFactoryInterface $formFactory,
        FormInterface $form,
        Session $session,
        FlashBagInterface $flashBag,
        RouterInterface $router
    ) {
        $userRepository->findUserByConfirmationToken('token12345')->willReturn($user);
        $user->isPasswordRequestNonExpired(Argument::any())->willReturn(true);

        $formFactory->create('admin_password_reset_change_password', $user)->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);

        $user->setConfirmationToken(null)->shouldBeCalled();
        $user->setPasswordRequestedAt(null)->shouldBeCalled();
        $userRepository->save($user)->shouldBeCalled();

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);

        $flashBag->add('success', 'Your password has been changed successfully')->shouldBeCalled();

        $router->generate('fsi_admin_security_user_login')->willReturn('url');

        $response = $this->changePasswordAction($request, 'token12345');
        $response->shouldHaveType('Symfony\Component\HttpFoundation\RedirectResponse');
    }
}
