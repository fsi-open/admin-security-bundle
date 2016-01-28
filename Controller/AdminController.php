<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Controller;

use FSi\Bundle\AdminBundle\Message\FlashMessages;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdminController
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    private $tokenStorage;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    private $templating;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
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
     * @param EngineInterface $templating
     * @param FormFactoryInterface $formFactory
     * @param TokenStorageInterface $tokenStorage
     * @param RouterInterface $router
     * @param EventDispatcherInterface $eventDispatcher
     * @param FlashMessages $flashMessages
     * @param string $changePasswordActionTemplate
     */
    public function __construct(
        EngineInterface $templating,
        FormFactoryInterface $formFactory,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages,
        $changePasswordActionTemplate
    ) {
        $this->templating = $templating;
        $this->formFactory = $formFactory;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->eventDispatcher = $eventDispatcher;
        $this->flashMessages = $flashMessages;
        $this->changePasswordActionTemplate = $changePasswordActionTemplate;
    }

    public function changePasswordAction(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!($user instanceof ChangeablePasswordInterface)) {
            throw new NotFoundHttpException();
        }

        $form = $this->formFactory->create('admin_change_password', $user);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->eventDispatcher->dispatch(
                AdminSecurityEvents::CHANGE_PASSWORD,
                new ChangePasswordEvent($user)
            );

            $this->flashMessages->success('admin.change_password_message.success', 'FSiAdminSecurity');

            return new RedirectResponse($this->router->generate('fsi_admin_security_user_login'));
        }

        return $this->templating->renderResponse(
            $this->changePasswordActionTemplate,
            array('form' => $form->createView())
        );
    }
}
