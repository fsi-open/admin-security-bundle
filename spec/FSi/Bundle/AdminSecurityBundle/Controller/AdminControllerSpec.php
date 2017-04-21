<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller;

use FSi\Bundle\AdminBundle\Message\FlashMessages;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AdminControllerSpec extends ObjectBehavior
{
    function let(
        EngineInterface $templating,
        FormFactoryInterface $formFactory,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages,
        FormInterface $form,
        Request $request
    ) {
        $form->handleRequest($request)->willReturn($form);
        $form->isSubmitted()->willReturn(true);
        $this->beConstructedWith(
            $templating,
            $formFactory,
            $tokenStorage,
            $router,
            $eventDispatcher,
            $flashMessages,
            'FSiAdminSecurityBundle:Admin:change_password.html.twig',
            'form_type',
            ['validation_group']
        );
    }

    function it_render_template_with_change_password_form(
        EngineInterface $templating,
        FormFactoryInterface $formFactory,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        ChangeablePasswordInterface $user,
        FormInterface $form,
        FormView $formView,
        Request $request,
        Response $response
    ) {
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

        $templating->renderResponse('FSiAdminSecurityBundle:Admin:change_password.html.twig', [
            'form' => $formView
        ])->shouldBeCalled()->willReturn($response);

        $this->changePasswordAction($request)->shouldReturn($response);
    }

    function it_dispatch_event_and_redirect_user_to_login_page_after_successful_form_validation(
        FormFactoryInterface $formFactory,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        ChangeablePasswordInterface $user,
        FormInterface $form,
        Request $request,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages
    ) {
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
            AdminSecurityEvents::CHANGE_PASSWORD,
            Argument::allOf(
                Argument::type('FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent'),
                Argument::which('getUser', $user->getWrappedObject())
            )
        )->shouldBeCalled();

        $flashMessages->success('admin.change_password_message.success', 'FSiAdminSecurity')->shouldBeCalled();

        $router->generate('fsi_admin_security_user_login')->shouldBeCalled()->willReturn('/admin/login');

        $response = $this->changePasswordAction($request);
        $response->shouldHaveType('Symfony\Component\HttpFoundation\RedirectResponse');
        $response->getTargetUrl()->shouldReturn('/admin/login');
    }
}
