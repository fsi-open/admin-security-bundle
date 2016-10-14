<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AdminControllerSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher
     * @param \FSi\Bundle\AdminBundle\Message\FlashMessages $flashMessages
     */
    function let($templating, $formFactory, $tokenStorage, $router, $eventDispatcher, $flashMessages)
    {
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

    /**
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface $user
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\Form\FormView $formView
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    function it_render_template_with_change_password_form(
        $templating, $formFactory, $tokenStorage, $token, $user, $form, $formView, $request, $response
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $formFactory->create(
            'form_type',
            $user,
            array('validation_groups' => array('validation_group'))
        )->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->shouldBeCalled()->willReturn(false);
        $form->createView()->shouldBeCalled()->willReturn($formView);

        $templating->renderResponse('FSiAdminSecurityBundle:Admin:change_password.html.twig', array(
            'form' => $formView
        ))->shouldBeCalled()->willReturn($response);

        $this->changePasswordAction($request)->shouldReturn($response);
    }

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface $user
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher
     * @param \FSi\Bundle\AdminBundle\Message\FlashMessages $flashMessages
     */
    function it_dispatch_event_and_redirect_user_to_login_page_after_successful_form_validation(
        $formFactory, $tokenStorage, $token, $user, $form, $request, $router, $eventDispatcher, $flashMessages
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $formFactory->create(
            'form_type',
            $user,
            array('validation_groups' => array('validation_group'))
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
