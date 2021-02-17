<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset;

use FSi\Bundle\AdminBundle\Message\FlashMessages;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class ChangePasswordController
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $changePasswordActionTemplate;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var FlashMessages
     */
    private $flashMessages;

    /**
     * @var string
     */
    private $formType;

    /**
     * @var array
     */
    private $formValidationGroups;

    public function __construct(
        Environment $twig,
        string $changePasswordActionTemplate,
        UserRepositoryInterface $userRepository,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages,
        string $formType,
        array $formValidationGroups
    ) {
        $this->twig = $twig;
        $this->changePasswordActionTemplate = $changePasswordActionTemplate;
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->flashMessages = $flashMessages;
        $this->formType = $formType;
        $this->formValidationGroups = $formValidationGroups;
    }

    public function changePasswordAction(Request $request, string $token): Response
    {
        $user = $this->userRepository->findUserByPasswordResetToken($token);

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        if (false === $user->getPasswordResetToken()->isNonExpired()) {
            throw new NotFoundHttpException();
        }

        $form = $this->formFactory->create(
            $this->formType,
            $user,
            ['validation_groups' => $this->formValidationGroups]
        );

        $form->handleRequest($request);
        if (true === $form->isSubmitted() && true === $form->isValid()) {
            $user->removePasswordResetToken();

            $this->eventDispatcher->dispatch(new ChangePasswordEvent($user), AdminSecurityEvents::CHANGE_PASSWORD);

            $this->flashMessages->success(
                'admin.password_reset.change_password.message.success',
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
