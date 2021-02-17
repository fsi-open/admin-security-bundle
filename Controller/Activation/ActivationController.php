<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Controller\Activation;

use FSi\Bundle\AdminBundle\Message\FlashMessages;
use FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\EnforceablePasswordChangeInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class ActivationController
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
    private $changePasswordFormType;

    /**
     * @var array
     */
    private $changePasswordFormValidationGroups;

    public function __construct(
        Environment $twig,
        string $changePasswordActionTemplate,
        UserRepositoryInterface $userRepository,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages,
        string $changePasswordFormType,
        array $changePasswordFormValidationGroups
    ) {
        $this->twig = $twig;
        $this->changePasswordActionTemplate = $changePasswordActionTemplate;
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->flashMessages = $flashMessages;
        $this->changePasswordFormType = $changePasswordFormType;
        $this->changePasswordFormValidationGroups = $changePasswordFormValidationGroups;
    }

    public function activateAction(string $token): Response
    {
        $user = $this->tryFindUserByActivationToken($token);

        if (true === $this->isUserEnforcedToChangePassword($user)) {
            $this->flashMessages->info(
                'admin.activation.message.change_password',
                [],
                'FSiAdminSecurity'
            );

            $response = new RedirectResponse(
                $this->router->generate('fsi_admin_activation_change_password', ['token' => $token])
            );
        } else {
            $this->eventDispatcher->dispatch(new ActivationEvent($user), AdminSecurityEvents::ACTIVATION);

            $response = $this->addFlashAndRedirect('success', 'admin.activation.message.success');
        }

        return $response;
    }

    public function changePasswordAction(Request $request, string $token): Response
    {
        $user = $this->tryFindUserByActivationToken($token);

        if (false === $this->isUserEnforcedToChangePassword($user)) {
            throw new NotFoundHttpException();
        }

        $form = $this->formFactory->create(
            $this->changePasswordFormType,
            $user,
            ['validation_groups' => $this->changePasswordFormValidationGroups]
        );

        $form->handleRequest($request);
        if (true === $form->isSubmitted() && true === $form->isValid()) {
            $this->eventDispatcher->dispatch(new ActivationEvent($user), AdminSecurityEvents::ACTIVATION);
            $this->eventDispatcher->dispatch(new ChangePasswordEvent($user), AdminSecurityEvents::CHANGE_PASSWORD);

            return $this->addFlashAndRedirect('success', 'admin.activation.message.change_password_success');
        }

        return new Response($this->twig->render(
            $this->changePasswordActionTemplate,
            ['form' => $form->createView()]
        ));
    }

    /**
     * @param string $token
     * @return ActivableInterface&ChangeablePasswordInterface
     */
    private function tryFindUserByActivationToken(string $token): ActivableInterface
    {
        $user = $this->userRepository->findUserByActivationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        if (true === $user->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (false === $user->getActivationToken()->isNonExpired()) {
            throw new NotFoundHttpException();
        }

        return $user;
    }

    private function addFlashAndRedirect(string $type, string $message): RedirectResponse
    {
        $this->flashMessages->{$type}($message, [], 'FSiAdminSecurity');

        return new RedirectResponse($this->router->generate('fsi_admin_security_user_login'));
    }

    private function isUserEnforcedToChangePassword(ActivableInterface $user): bool
    {
        return true === $user instanceof EnforceablePasswordChangeInterface
            && true === $user->isForcedToChangePassword()
        ;
    }
}
