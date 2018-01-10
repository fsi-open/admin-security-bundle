<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset;

use FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset\ChangePasswordController;
use FSi\Bundle\AdminBundle\Message\FlashMessages;
use FSi\Bundle\AdminSecurityBundle\Security\Token\TokenInterface;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class ChangePasswordControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ChangePasswordController::class);
    }

    function let(
        EngineInterface $templating,
        UserRepositoryInterface $userRepository,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages
    ) {
        $this->beConstructedWith(
            $templating,
            'template-name',
            $userRepository,
            $router,
            $formFactory,
            $eventDispatcher,
            $flashMessages,
            'form_type',
            ['validation_group']
        );
    }

    function it_changes_password(
        Request $request,
        UserRepositoryInterface $userRepository,
        UserInterface $user,
        TokenInterface $token,
        FormFactoryInterface $formFactory,
        FormInterface $form,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages
    ) {
        $form->handleRequest($request)->willReturn($form);
        $form->isSubmitted()->willReturn(true);
        $userRepository->findUserByPasswordResetToken('token12345')->willReturn($user);
        $user->getPasswordResetToken()->willReturn($token);
        $token->isNonExpired()->willReturn(true);

        $formFactory->create(
            'form_type',
            $user,
            ['validation_groups' => ['validation_group']]
        )->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);

        $user->removePasswordResetToken()->shouldBeCalled();
        $eventDispatcher->dispatch(
            AdminSecurityEvents::CHANGE_PASSWORD,
            Argument::allOf(
                Argument::type(ChangePasswordEvent::class),
                Argument::which('getUser', $user->getWrappedObject())
            )
        )->shouldBeCalled();

        $flashMessages->success(
            'admin.password_reset.change_password.message.success',
            [],
            'FSiAdminSecurity'
        )->shouldBeCalled();

        $router->generate('fsi_admin_security_user_login')->willReturn('url');

        $response = $this->changePasswordAction($request, 'token12345');
        $response->shouldHaveType(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('url');
    }
}
