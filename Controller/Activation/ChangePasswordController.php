<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Controller\Activation;

use FSi\Bundle\AdminBundle\Message\FlashMessages;
use FSi\Bundle\AdminSecurityBundle\Controller\FlashWithRedirect;
use FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent;
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class ChangePasswordController
{
    use ActivableUser;
    use FlashWithRedirect;

    private Environment $twig;
    private UserRepositoryInterface $userRepository;
    private ClockInterface $clock;
    private UrlGeneratorInterface $urlGenerator;
    private FormFactoryInterface $formFactory;
    private EventDispatcherInterface $eventDispatcher;
    private FlashMessages $flashMessages;
    private string $changePasswordActionTemplate;
    private string $changePasswordFormType;
    /**
     * @var array<string>
     */
    private array $changePasswordFormValidationGroups;

    /**
     * @param array<string> $changePasswordFormValidationGroups
     */
    public function __construct(
        Environment $twig,
        UserRepositoryInterface $userRepository,
        ClockInterface $clock,
        UrlGeneratorInterface $urlGenerator,
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages,
        string $changePasswordActionTemplate,
        string $changePasswordFormType,
        array $changePasswordFormValidationGroups
    ) {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
        $this->clock = $clock;
        $this->urlGenerator = $urlGenerator;
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->flashMessages = $flashMessages;
        $this->changePasswordActionTemplate = $changePasswordActionTemplate;
        $this->changePasswordFormType = $changePasswordFormType;
        $this->changePasswordFormValidationGroups = $changePasswordFormValidationGroups;
    }

    public function __invoke(Request $request, string $token): Response
    {
        $user = $this->tryFindUserByActivationToken($token);
        if (false === $this->isUserEnforcedToChangePassword($user)) {
            throw new NotFoundHttpException();
        }

        $form = $this->formFactory->create(
            $this->changePasswordFormType,
            $user,
            ['validation_groups' => $this->changePasswordFormValidationGroups]
        );

        $form->handleRequest($request);
        if (true === $form->isSubmitted() && true === $form->isValid()) {
            $this->eventDispatcher->dispatch(new ActivationEvent($user));
            $this->eventDispatcher->dispatch(new ChangePasswordEvent($user));

            return $this->addFlashAndRedirect('success', 'admin.activation.message.change_password_success');
        }

        return new Response(
            $this->twig->render(
                $this->changePasswordActionTemplate,
                ['form' => $form->createView()]
            )
        );
    }
}
