<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Controller\Activation;

use FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserEnforcePasswordChangeInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class ActivationController
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
     * @param EngineInterface $templating
     * @param string $changePasswordActionTemplate
     * @param UserRepositoryInterface $userRepository
     * @param RouterInterface $router
     * @param FormFactoryInterface $formFactory
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EngineInterface $templating,
        $changePasswordActionTemplate,
        UserRepositoryInterface $userRepository,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->templating = $templating;
        $this->changePasswordActionTemplate = $changePasswordActionTemplate;
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Request $request
     * @param string $token
     * @return RedirectResponse
     */
    public function activateAction(Request $request, $token)
    {
        $user = $this->tryFindUserByActivationToken($token);

        if ($this->isUserEnforcedToChangePassword($user)) {
            $request->getSession()->getFlashBag()->add(
                'info',
                'admin.activation.message.change_password'
            );

            return new RedirectResponse($this->router->generate('fsi_admin_activation_change_password', array('token' => $token)));
        } else {
            $this->eventDispatcher->dispatch(
                AdminSecurityEvents::ACTIVATION,
                new ActivationEvent($user)
            );

            return $this->addFlashAndRedirect(
                $request,
                'success',
                'admin.activation.message.success'
            );
        }
    }

    /**
     * @param Request $request
     * @param string $token
     * @return RedirectResponse|Response
     */
    public function changePasswordAction(Request $request, $token)
    {
        $user = $this->tryFindUserByActivationToken($token);

        if (!$this->isUserEnforcedToChangePassword($user)) {
            throw new NotFoundHttpException();
        }

        $form = $this->formFactory->create('admin_password_reset_change_password', $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->eventDispatcher->dispatch(
                AdminSecurityEvents::ACTIVATION,
                new ActivationEvent($user)
            );

            $this->eventDispatcher->dispatch(
                AdminSecurityEvents::CHANGE_PASSWORD,
                new ChangePasswordEvent($user)
            );

            return $this->addFlashAndRedirect(
                $request,
                'success',
                'admin.activation.message.change_password_success'
            );
        }

        return $this->templating->renderResponse(
            $this->changePasswordActionTemplate,
            array('form' => $form->createView())
        );
    }

    /**
     * @param $token
     * @return UserActivableInterface|null
     */
    private function tryFindUserByActivationToken($token)
    {
        $user = $this->userRepository->findUserByActivationToken($token);

        if (!($user instanceof UserActivableInterface)) {
            throw new NotFoundHttpException();
        }

        if ($user->isEnabled()) {
            throw new NotFoundHttpException();
        }

        if (!$user->getActivationToken()->isNonExpired()) {
            throw new NotFoundHttpException();
        }

        return $user;
    }

    /**
     * @param Request $request
     * @param string $type
     * @param string $message
     * @return RedirectResponse
     */
    private function addFlashAndRedirect(Request $request, $type, $message)
    {
        $request->getSession()->getFlashBag()->add($type, $message);

        return new RedirectResponse($this->router->generate('fsi_admin_security_user_login'));
    }

    /**
     * @param $user
     * @return bool
     */
    private function isUserEnforcedToChangePassword($user)
    {
        return ($user instanceof UserEnforcePasswordChangeInterface) && $user->isForcedToChangePassword();
    }
}
