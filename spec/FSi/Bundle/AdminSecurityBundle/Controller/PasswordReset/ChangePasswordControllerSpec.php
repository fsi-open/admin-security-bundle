<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
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
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface $userRepository
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    function let($templating, $userRepository, $router, $formFactory, $eventDispatcher)
    {
        $this->beConstructedWith(
            $templating,
            'template-name',
            $userRepository,
            $router,
            $formFactory,
            $eventDispatcher
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface $userRepository
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     * @param \FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface $token
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     * @param \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $flashBag
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    function it_changes_password(
        $request, $userRepository, $user, $token, $formFactory, $form, $session, $flashBag, $router, $eventDispatcher
    ) {
        $userRepository->findUserByConfirmationToken('token12345')->willReturn($user);
        $user->getPasswordResetToken()->willReturn($token);
        $token->isNonExpired()->willReturn(true);

        $formFactory->create('admin_password_reset_change_password', $user)->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);

        $user->removePasswordResetToken()->shouldBeCalled();
        $eventDispatcher->dispatch(
            AdminSecurityEvents::CHANGE_PASSWORD,
            Argument::allOf(
                Argument::type('FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent'),
                Argument::which('getUser', $user->getWrappedObject())
            )
        )->shouldBeCalled();

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);

        $flashBag->add('success', 'admin.password_reset.change_password.message.success')->shouldBeCalled();

        $router->generate('fsi_admin_security_user_login')->willReturn('url');

        $response = $this->changePasswordAction($request, 'token12345');
        $response->shouldHaveType('Symfony\Component\HttpFoundation\RedirectResponse');
        $response->getTargetUrl()->shouldReturn('url');
    }
}
