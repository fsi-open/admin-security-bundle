<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Controller;

use FSi\Bundle\AdminBundle\Message\FlashMessages;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class AdminController
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var string
     */
    private $changePasswordActionTemplate;

    /**
     * @var FlashMessages
     */
    private $flashMessages;

    /**
     * @var string
     */
    private $changePasswordFormType;

    /**
     * @var array
     */
    private $changePasswordFormValidationGroups;

    public function __construct(
        Environment $twig,
        FormFactoryInterface $formFactory,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages,
        string $changePasswordActionTemplate,
        string $changePasswordFormType,
        array $changePasswordFormValidationGroups
    ) {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->eventDispatcher = $eventDispatcher;
        $this->flashMessages = $flashMessages;
        $this->changePasswordActionTemplate = $changePasswordActionTemplate;
        $this->changePasswordFormType = $changePasswordFormType;
        $this->changePasswordFormValidationGroups = $changePasswordFormValidationGroups;
    }

    public function changePasswordAction(Request $request): Response
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (false === $user instanceof ChangeablePasswordInterface) {
            throw new NotFoundHttpException();
        }

        $form = $this->formFactory->create(
            $this->changePasswordFormType,
            $user,
            ['validation_groups' => $this->changePasswordFormValidationGroups]
        );

        $form->handleRequest($request);
        if (true === $form->isSubmitted() && true === $form->isValid()) {
            $this->eventDispatcher->dispatch(new ChangePasswordEvent($user), AdminSecurityEvents::CHANGE_PASSWORD);

            $this->flashMessages->success(
                'admin.change_password_message.success',
                [],
                'FSiAdminSecurity'
            );

            return new RedirectResponse($this->router->generate('fsi_admin_security_user_login'));
        }

        return new Response($this->twig->render(
            $this->changePasswordActionTemplate,
            ['form' => $form->createView()]
        ));
    }
}
