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
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Twig\Environment;

use function get_class;

class ResetRequestController
{
    /**
     * @var Environment
     */
    private $twig;

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
        Environment $twig,
        $requestActionTemplate,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        UserRepositoryInterface $userRepository,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages,
        $formType
    ) {
        $this->twig = $twig;
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

        $form->handleRequest($request);
        if (false === $form->isSubmitted() || false === $form->isValid()) {
            return new Response($this->twig->render($this->requestActionTemplate, ['form' => $form->createView()]));
        }

        $user = $this->getUser($form);
        $redirectResponse = $this->addFlashAndRedirect('info', 'admin.password_reset.request.mail_sent_if_correct');

        if (false === $this->isUserEligibleForResettingPassword($user)) {
            return $redirectResponse;
        }

        $this->eventDispatcher->dispatch(
            new ResetPasswordRequestEvent($user),
            AdminSecurityEvents::RESET_PASSWORD_REQUEST
        );

        return $redirectResponse;
    }

    private function addFlashAndRedirect(string $type, string $message): RedirectResponse
    {
        $this->flashMessages->{$type}($message, [], 'FSiAdminSecurity');

        return new RedirectResponse($this->router->generate('fsi_admin_security_user_login'));
    }

    private function getUser(FormInterface $form): ?UserInterface
    {
        $user = $this->userRepository->findUserByEmail($form->get('email')->getData());
        if (null === $user) {
            return null;
        }

        if (false === $user instanceof UserInterface) {
            throw new RuntimeException(
                sprintf('Expected instance of %s but got instance of %s', UserInterface::class, get_class($user))
            );
        }

        return $user;
    }

    private function isUserEligibleForResettingPassword($user): bool
    {
        if (false === $user instanceof ResettablePasswordInterface) {
            return false;
        }

        if (true === $user instanceof AdvancedUserInterface && false === $user->isEnabled()) {
            return false;
        }

        if (true === $this->hasNonExpiredPasswordResetToken($user)) {
            return false;
        }

        if (true === $user instanceof AdvancedUserInterface && false === $user->isAccountNonLocked()) {
            return false;
        }

        return true;
    }

    private function hasNonExpiredPasswordResetToken(ResettablePasswordInterface $user): bool
    {
        return $user->getPasswordResetToken() && $user->getPasswordResetToken()->isNonExpired();
    }
}
