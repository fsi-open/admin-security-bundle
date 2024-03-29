<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Controller;

use FSi\Bundle\AdminBundle\Message\FlashMessages;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class ChangePasswordController
{
    private TokenStorageInterface $tokenStorage;
    private RouterInterface $router;
    private EventDispatcherInterface $eventDispatcher;
    private Environment $twig;
    private FormFactoryInterface $formFactory;
    private FlashMessages $flashMessages;
    private string $changePasswordActionTemplate;
    /**
     * @var class-string<FormInterface<FormInterface>>
     */
    private string $changePasswordFormType;
    /**
     * @var array<string>
     */
    private array $changePasswordFormValidationGroups;

    /**
     * @param class-string<FormInterface<FormInterface>> $changePasswordFormType
     * @param array<string> $changePasswordFormValidationGroups
     */
    public function __construct(
        Environment $twig,
        FormFactoryInterface $formFactory,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages,
        string $changePasswordActionTemplate,
        string $changePasswordFormType,
        array $changePasswordFormValidationGroups
    ) {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->eventDispatcher = $eventDispatcher;
        $this->flashMessages = $flashMessages;
        $this->changePasswordActionTemplate = $changePasswordActionTemplate;
        $this->changePasswordFormType = $changePasswordFormType;
        $this->changePasswordFormValidationGroups = $changePasswordFormValidationGroups;
    }

    public function __invoke(Request $request): Response
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            throw new NotFoundHttpException();
        }

        $user = $token->getUser();
        if (false === $user instanceof ChangeablePasswordInterface) {
            throw new NotFoundHttpException();
        }

        $form = $this->formFactory->create(
            $this->changePasswordFormType,
            $user,
            ['validation_groups' => $this->changePasswordFormValidationGroups]
        );

        $form->handleRequest($request);
        if (true === $form->isSubmitted() && true === $form->isValid()) {
            $this->eventDispatcher->dispatch(new ChangePasswordEvent($user));

            $this->flashMessages->success(
                'admin.change_password_message.success',
                [],
                'FSiAdminSecurity'
            );

            return new RedirectResponse($this->router->generate('fsi_admin_security_user_login'));
        }

        return new Response($this->twig->render(
            $this->changePasswordActionTemplate,
            ['form' => $form->createView()]
        ));
    }
}
