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
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class ChangePasswordController
{
    /**
     * @var EngineInterface
     */
    private $templating;

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

    /**
     * @param EngineInterface $templating
     * @param string $changePasswordActionTemplate
     * @param UserRepositoryInterface $userRepository
     * @param RouterInterface $router
     * @param FormFactoryInterface $formFactory
     * @param EventDispatcherInterface $eventDispatcher
     * @param FlashMessages $flashMessages
     * @param string $formType
     * @param array $formValidationGroups
     */
    public function __construct(
        EngineInterface $templating,
        $changePasswordActionTemplate,
        UserRepositoryInterface $userRepository,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages,
        $formType,
        array $formValidationGroups
    ) {
        $this->templating = $templating;
        $this->changePasswordActionTemplate = $changePasswordActionTemplate;
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->flashMessages = $flashMessages;
        $this->formType = $formType;
        $this->formValidationGroups = $formValidationGroups;
    }

    /**
     * @param Request $request
     * @param string $token
     * @return RedirectResponse|Response
     */
    public function changePasswordAction(Request $request, $token)
    {
        $user = $this->userRepository->findUserByPasswordResetToken($token);
        if (!($user instanceof ResettablePasswordInterface)) {
            throw new NotFoundHttpException();
        }

        if (!$user->getPasswordResetToken()->isNonExpired()) {
            throw new NotFoundHttpException();
        }

        $form = $this->formFactory->create(
            $this->formType,
            $user,
            array('validation_groups' => $this->formValidationGroups)
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user->removePasswordResetToken();

            $this->eventDispatcher->dispatch(
                AdminSecurityEvents::CHANGE_PASSWORD,
                new ChangePasswordEvent($user)
            );

            $this->flashMessages->success('admin.password_reset.change_password.message.success', 'FSiAdminSecurity');

            return new RedirectResponse($this->router->generate('fsi_admin_security_user_login'));
        }

        return $this->templating->renderResponse(
            $this->changePasswordActionTemplate,
            array('form' => $form->createView())
        );
    }
}
