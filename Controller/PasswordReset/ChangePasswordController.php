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
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class ChangePasswordController
{
    private Environment $twig;
    private UserRepositoryInterface $userRepository;
    private ClockInterface $clock;
    private RouterInterface $router;
    private FormFactoryInterface $formFactory;
    private EventDispatcherInterface $eventDispatcher;
    private FlashMessages $flashMessages;
    private string $changePasswordActionTemplate;
    /**
     * @var class-string<FormInterface<FormInterface>>
     */
    private string $formType;
    /**
     * @var array<string>
     */
    private array $formValidationGroups;

    /**
     * @param class-string<FormInterface<FormInterface>> $formType
     * @param array<string> $formValidationGroups
     */
    public function __construct(
        Environment $twig,
        UserRepositoryInterface $userRepository,
        ClockInterface $clock,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages,
        string $changePasswordActionTemplate,
        string $formType,
        array $formValidationGroups
    ) {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
        $this->clock = $clock;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->flashMessages = $flashMessages;
        $this->changePasswordActionTemplate = $changePasswordActionTemplate;
        $this->formType = $formType;
        $this->formValidationGroups = $formValidationGroups;
    }

    public function __invoke(Request $request, string $token): Response
    {
        $user = $this->userRepository->findUserByPasswordResetToken($token);
        if (null === $user) {
            throw new NotFoundHttpException();
        }

        $this->validatePasswordResetToken($user);

        $form = $this->formFactory->create(
            $this->formType,
            $user,
            ['validation_groups' => $this->formValidationGroups]
        );

        $form->handleRequest($request);
        if (true === $form->isSubmitted() && true === $form->isValid()) {
            return $this->handleFormSubmitAndRedirect($user);
        }

        return new Response($this->twig->render(
            $this->changePasswordActionTemplate,
            ['form' => $form->createView()]
        ));
    }

    /**
     * @throws NotFoundHttpException
     */
    private function validatePasswordResetToken(ResettablePasswordInterface $user): void
    {
        $passwordResetToken = $user->getPasswordResetToken();
        if (null === $passwordResetToken) {
            throw new NotFoundHttpException();
        }

        if (false === $passwordResetToken->isNonExpired($this->clock)) {
            throw new NotFoundHttpException();
        }
    }

    private function handleFormSubmitAndRedirect(ResettablePasswordInterface $user): Response
    {
        $user->removePasswordResetToken();

        $this->eventDispatcher->dispatch(new ChangePasswordEvent($user));
        $this->flashMessages->success(
            'admin.password_reset.change_password.message.success',
            [],
            'FSiAdminSecurity'
        );

        return new RedirectResponse($this->router->generate('fsi_admin_security_user_login'));
    }
}
