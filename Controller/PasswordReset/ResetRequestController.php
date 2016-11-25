<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    public function __construct(
        EngineInterface $templating,
        $requestActionTemplate,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        UserRepositoryInterface $userRepository,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages
    ) {
        $this->templating = $templating;
        $this->requestActionTemplate = $requestActionTemplate;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->flashMessages = $flashMessages;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function requestAction(Request $request)
    {
        $form = $this->formFactory->create('admin_password_reset_request');
        $form->handleRequest($request);

        if ($form->isValid()) {

            $user = $this->getUser($form);
            if (!($user instanceof ResettablePasswordInterface)) {
                return $this->addFlashAndRedirect('info', 'admin.password_reset.request.mail_sent_if_correct');
            }

            if ($this->hasNonExpiredPasswordResetToken($user)) {
                return $this->addFlashAndRedirect('warning', 'admin.password_reset.request.already_requested');
            }

            if (($user instanceof AdvancedUserInterface) && !$user->isAccountNonLocked()) {
                return $this->addFlashAndRedirect('warning', 'admin.password_reset.request.account_locked');
            }

            $this->eventDispatcher->dispatch(
                AdminSecurityEvents::RESET_PASSWORD_REQUEST,
                new ResetPasswordRequestEvent($user)
            );

            return $this->addFlashAndRedirect('info', 'admin.password_reset.request.mail_sent_if_correct');
        }

        return $this->templating->renderResponse(
            $this->requestActionTemplate,
            array('form' => $form->createView())
        );
    }

    /**
     * @param string $type
     * @param string $message
     * @return RedirectResponse
     */
    private function addFlashAndRedirect($type, $message)
    {
        $this->flashMessages->{$type}($message, 'FSiAdminSecurity');

        return new RedirectResponse($this->router->generate('fsi_admin_security_user_login'));
    }

    /**
     * @param FormInterface $form
     * @return ResettablePasswordInterface|null
     */
    private function getUser(FormInterface $form)
    {
        return $this->userRepository->findUserByEmail($form->get('email')->getData());
    }

    /**
     * @param ResettablePasswordInterface $user
     * @return bool
     */
    private function hasNonExpiredPasswordResetToken(ResettablePasswordInterface $user)
    {
        return $user->getPasswordResetToken() && $user->getPasswordResetToken()->isNonExpired();
    }
}
