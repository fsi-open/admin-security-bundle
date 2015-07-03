<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChangePasswordControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset\ChangePasswordController');
    }

    /**
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \FSi\Bundle\AdminSecurityBundle\Model\UserRepositoryInterface $userRepository
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     */
    function let($templating, $userRepository, $router, $formFactory)
    {
        $this->beConstructedWith($templating, 'template-name', $userRepository, $router, $formFactory, 3600 * 12);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Bundle\AdminSecurityBundle\Model\UserRepositoryInterface $userRepository
     * @param \FSi\Bundle\AdminSecurityBundle\Model\UserPasswordResetInterface $user
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     * @param \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $flashBag
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    function it_changes_password(
        $request, $userRepository, $user, $formFactory, $form, $session, $flashBag, $router
    ) {
        $userRepository->findUserByConfirmationToken('token12345')->willReturn($user);
        $user->isPasswordRequestNonExpired(3600 * 12)->willReturn(true);

        $formFactory->create('admin_password_reset_change_password', $user)->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);

        $user->setConfirmationToken(null)->shouldBeCalled();
        $user->setPasswordRequestedAt(null)->shouldBeCalled();
        $userRepository->save($user)->shouldBeCalled();

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);

        $flashBag->add('success', 'admin.change_password_message.success')->shouldBeCalled();

        $router->generate('fsi_admin_security_user_login')->willReturn('url');

        $response = $this->changePasswordAction($request, 'token12345');
        $response->shouldHaveType('Symfony\Component\HttpFoundation\RedirectResponse');
    }
}
