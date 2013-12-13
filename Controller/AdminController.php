<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class AdminController
{
    private $securityContext;
    private $router;
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    private $templating;

    /**
     * @var \Symfony\Component\Form\FormInterface
     */
    private $changePasswordForm;

    /**
     * @param EngineInterface $templating
     * @param FormInterface $changePasswordForm
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
     */
    public function __construct(
        EngineInterface $templating,
        FormInterface $changePasswordForm,
        SecurityContext $securityContext,
        Router $router
    ) {
        $this->templating = $templating;
        $this->changePasswordForm = $changePasswordForm;
        $this->securityContext = $securityContext;
        $this->router = $router;
    }

    public function changePasswordAction(Request $request)
    {
        $this->changePasswordForm->handleRequest($request);

        if ($this->changePasswordForm->isValid()) {
            $request->getSession()->invalidate();
            $this->securityContext->setToken(null);

            $request->getSession()->getFlashBag()->set(
                'success',
                'admin.change_password_message.success'
            );

            return new RedirectResponse($this->router->generate('admin_security_user_login'));
        }

        return $this->templating->renderResponse('FSiAdminSecurityBundle:Admin:change_password.html.twig', array(
            'form' => $this->changePasswordForm->createView()
        ));
    }
}