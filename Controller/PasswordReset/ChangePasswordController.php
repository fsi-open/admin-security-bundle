<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    public function __construct(
        EngineInterface $templating,
        $changePasswordActionTemplate,
        UserManagerInterface $userManager,
        RouterInterface $router,
        FormFactoryInterface $formFactory
    ) {
        $this->templating = $templating;
        $this->changePasswordActionTemplate = $changePasswordActionTemplate;
        $this->userManager = $userManager;
        $this->router = $router;
        $this->formFactory = $formFactory;
    }

    public function changePasswordAction(Request $request, $token)
    {
        /** @var $user UserInterface */
        $user = $this->userManager->findUserByConfirmationToken($token);
        if (null === $user) {
            throw new NotFoundHttpException();
        }

        if (!$user->isPasswordRequestNonExpired(3600 * 12)) { // 12h FIXME: introduce parameter
            throw new NotFoundHttpException();
        }

        $form = $this->formFactory->create('admin_password_reset_change_password', $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $user->setConfirmationToken(null);
            $user->setPasswordRequestedAt(null);
            $this->userManager->updateUser($user);

            $request->getSession()->getFlashBag()->add(
                'success',
                'Your password has been changed successfully'
            );

            return new RedirectResponse($this->router->generate('fsi_admin_security_user_login'));
        }

        return $this->templating->renderResponse(
            $this->changePasswordActionTemplate,
            array('form' => $form->createView())
        );
    }
}
