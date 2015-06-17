<?php

namespace FSi\Bundle\AdminSecurityBundle\Controller\PasswordReset;

use FSi\Bundle\AdminSecurityBundle\Mailer\MailerInterface;
use FSi\Bundle\AdminSecurityBundle\Model\UserPasswordResetInterface;
use FSi\Bundle\AdminSecurityBundle\Model\UserRepositoryInterface;
use FSi\Bundle\AdminSecurityBundle\Token\TokenGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

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
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(
        EngineInterface $templating,
        $requestActionTemplate,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        UserRepositoryInterface $userRepository,
        TokenGeneratorInterface $tokenGenerator,
        MailerInterface $mailer
    ) {
        $this->templating = $templating;
        $this->requestActionTemplate = $requestActionTemplate;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
    }

    public function requestAction(Request $request)
    {
        $form = $this->formFactory->create('admin_password_reset_request');
        $form->handleRequest($request);

        if ($form->isValid()) {

            /** @var UserPasswordResetInterface $user */
            $user = $this->userRepository->findUserByEmail($form->get('email')->getData());
            if (null === $user) {
                return $this->addFlashAndRedirect($request);
            }

//            if ($user->isPasswordRequestNonExpired(1234)) {
//
//            }

            $user->setConfirmationToken($this->tokenGenerator->generateToken());
            $user->setPasswordRequestedAt(new \DateTime());

            $this->userRepository->save($user);

            $this->mailer->sendPasswordResetMail($user);

            return $this->addFlashAndRedirect($request);
        }

        return $this->templating->renderResponse(
            $this->requestActionTemplate,
            array('form' => $form->createView())
        );
    }

    private function addFlashAndRedirect(Request $request)
    {
        $request->getSession()->getFlashBag()->add('password-reset.success', 'Reset password instructions sent');

        return new RedirectResponse($this->router->generate('fsi_admin_security_password_reset_request'));
    }
}
