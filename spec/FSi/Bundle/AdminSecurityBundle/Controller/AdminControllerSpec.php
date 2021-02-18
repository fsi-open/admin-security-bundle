<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller;

use FSi\Bundle\AdminBundle\Message\FlashMessages;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Twig\Environment;

class AdminControllerSpec extends ObjectBehavior
{
    public function let(
        Environment $twig,
        FormFactoryInterface $formFactory,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages,
        FormInterface $form,
        Request $request
    ): void {
        $form->handleRequest($request)->willReturn($form);
        $form->isSubmitted()->willReturn(true);
        $this->beConstructedWith(
            $twig,
            $formFactory,
            $tokenStorage,
            $router,
            $eventDispatcher,
            $flashMessages,
            '@FSiAdminSecurity/Admin/change_password.html.twig',
            'form_type',
            ['validation_group']
        );
    }

    public function it_render_template_with_change_password_form(
        Environment $twig,
        FormFactoryInterface $formFactory,
        TokenStorageInterface $tokenStorage,
        AbstractToken $token,
        ChangeablePasswordInterface $user,
        FormInterface $form,
        FormView $formView,
        Request $request
    ): void {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $formFactory->create(
            'form_type',
            $user,
            ['validation_groups' => ['validation_group']]
        )->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->shouldBeCalled()->willReturn(false);
        $form->createView()->shouldBeCalled()->willReturn($formView);

        $twig->render(
            '@FSiAdminSecurity/Admin/change_password.html.twig',
            ['form' => $formView]
        )->shouldBeCalled()->willReturn('response');

        $this->changePasswordAction($request)->getContent()->shouldReturn('response');
    }

    public function it_dispatch_event_and_redirect_user_to_login_page_after_successful_form_validation(
        FormFactoryInterface $formFactory,
        TokenStorageInterface $tokenStorage,
        AbstractToken $token,
        ChangeablePasswordInterface $user,
        FormInterface $form,
        Request $request,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages
    ): void {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $formFactory->create(
            'form_type',
            $user,
            ['validation_groups' => ['validation_group']]
        )->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);

        $token->getUser()->shouldBeCalled()->willReturn($user);
        $eventDispatcher->dispatch(
            Argument::allOf(
                Argument::type(ChangePasswordEvent::class),
                Argument::which('getUser', $user->getWrappedObject())
            ),
            AdminSecurityEvents::CHANGE_PASSWORD
        )->shouldBeCalled();

        $flashMessages->success(
            'admin.change_password_message.success',
            [],
            'FSiAdminSecurity'
        )->shouldBeCalled();

        $router->generate('fsi_admin_security_user_login')->shouldBeCalled()->willReturn('/admin/login');

        $response = $this->changePasswordAction($request);
        $response->shouldHaveType(RedirectResponse::class);
        $response->getTargetUrl()->shouldReturn('/admin/login');
    }
}
