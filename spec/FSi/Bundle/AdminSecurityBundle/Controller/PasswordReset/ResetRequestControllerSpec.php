<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface $userRepository
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \FSi\Bundle\AdminBundle\Message\FlashMessages $flashMessages
     */
    function let($templating, $formFactory, $router, $userRepository, $eventDispatcher, $flashMessages)
    {
        $this->beConstructedWith(
            $templating,
            'template_path',
            $formFactory,
            $router,
            $userRepository,
            $eventDispatcher,
            $flashMessages
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\Form\FormInterface $form2
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface $userRepository
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \FSi\Bundle\AdminBundle\Message\FlashMessages $flashMessages
     */
    function it_updates_confirmation_token_and_sends_mail(
        $request,
        $formFactory,
        $form,
        $form2,
        $userRepository,
        $user,
        $eventDispatcher,
        $router,
        $flashMessages
    ) {
        $formFactory->create('admin_password_reset_request')->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);

        $form->get('email')->willReturn($form2);
        $form2->getData()->willReturn('admin@fsi.pl');

        $userRepository->findUserByEmail('admin@fsi.pl')->willReturn($user);

        $user->getPasswordResetToken()->willReturn(null);
        $user->isAccountNonLocked()->willReturn(true);

        $eventDispatcher->dispatch(
            AdminSecurityEvents::RESET_PASSWORD_REQUEST,
            Argument::allOf(
                Argument::type('FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent'),
                Argument::which('getUser', $user->getWrappedObject())
            )
        )->shouldBeCalled();

        $flashMessages->success('admin.password_reset.request.mail_sent', 'FSiAdminSecurity')->shouldBeCalled();

        $router->generate('fsi_admin_security_user_login')->willReturn('url');

        $response = $this->requestAction($request);
        $response->shouldHaveType('Symfony\Component\HttpFoundation\RedirectResponse');
    }
}
