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
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ResetRequestController
{
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var string
     */
    private $requestActionTemplate;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

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

    public function __construct(
        EngineInterface $templating,
        $requestActionTemplate,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        UserRepositoryInterface $userRepository,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages,
        $formType
    ) {
        $this->templating = $templating;
        $this->requestActionTemplate = $requestActionTemplate;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->flashMessages = $flashMessages;
        $this->formType = $formType;
    }

    public function requestAction(Request $request): Response
    {
        $form = $this->formFactory->create($this->formType);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $user = $this->getUser($form);
            $redirectResponse = $this->addFlashAndRedirect(
                'info',
                'admin.password_reset.request.mail_sent_if_correct'
            );

            if (!($user instanceof ResettablePasswordInterface)) {
                return $redirectResponse;
            }

            if (($user instanceof AdvancedUserInterface) && !$user->isEnabled()) {
                return $redirectResponse;
            }

            if ($this->hasNonExpiredPasswordResetToken($user)) {
                return $redirectResponse;
            }

            if (($user instanceof AdvancedUserInterface) && !$user->isAccountNonLocked()) {
                return $redirectResponse;
            }

            $this->eventDispatcher->dispatch(
                AdminSecurityEvents::RESET_PASSWORD_REQUEST,
                new ResetPasswordRequestEvent($user)
            );

            return $redirectResponse;
        }

        return $this->templating->renderResponse(
            $this->requestActionTemplate,
            ['form' => $form->createView()]
        );
    }

    private function addFlashAndRedirect(string $type, string $message): RedirectResponse
    {
        $this->flashMessages->{$type}($message, [], 'FSiAdminSecurity');

        return new RedirectResponse($this->router->generate('fsi_admin_security_user_login'));
    }

    private function getUser(FormInterface $form): UserInterface
    {
        return $this->userRepository->findUserByEmail($form->get('email')->getData());
    }

    private function hasNonExpiredPasswordResetToken(ResettablePasswordInterface $user): bool
    {
        return $user->getPasswordResetToken() && $user->getPasswordResetToken()->isNonExpired();
    }
}
