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
use FSi\Bundle\AdminSecurityBundle\Controller\FlashWithRedirect;
use FSi\Bundle\AdminSecurityBundle\Event\ResetPasswordRequestEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\ResettablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function get_class;

class ResetRequestController
{
    use FlashWithRedirect;

    private Environment $twig;
    private FormFactoryInterface $formFactory;
    private ClockInterface $clock;
    private UrlGeneratorInterface $urlGenerator;
    private UserRepositoryInterface $userRepository;
    private EventDispatcherInterface $eventDispatcher;
    private FlashMessages $flashMessages;

    /**
     * @var class-string<FormInterface<FormInterface>>
     */
    private string $formType;
    private string $requestActionTemplate;

    /**
     * @param class-string<FormInterface<FormInterface>> $formType
     */
    public function __construct(
        Environment $twig,
        FormFactoryInterface $formFactory,
        ClockInterface $clock,
        UrlGeneratorInterface $urlGenerator,
        UserRepositoryInterface $userRepository,
        EventDispatcherInterface $eventDispatcher,
        FlashMessages $flashMessages,
        string $requestActionTemplate,
        string $formType
    ) {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->clock = $clock;
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->flashMessages = $flashMessages;
        $this->requestActionTemplate = $requestActionTemplate;
        $this->formType = $formType;
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->create($this->formType);

        $form->handleRequest($request);
        if (true === $form->isSubmitted() && true === $form->isValid()) {
            $user = $this->getUser($form);
            if (
                false === $user instanceof ResettablePasswordInterface
                || false === $this->isUserEligibleForResettingPassword($user)
            ) {
                return $this->addFlashAndRedirect('info', 'admin.password_reset.request.mail_sent_if_correct');
            }

            $this->eventDispatcher->dispatch(new ResetPasswordRequestEvent($user));
            return $this->addFlashAndRedirect('info', 'admin.password_reset.request.mail_sent_if_correct');
        }

        return new Response(
            $this->twig->render($this->requestActionTemplate, ['form' => $form->createView()])
        );
    }

    /**
     * @param FormInterface<FormInterface> $form
     * @return UserInterface|null
     * @throws RuntimeException
     */
    private function getUser(FormInterface $form): ?UserInterface
    {
        $user = $this->userRepository->findUserByEmail($form->get('email')->getData());
        if (null === $user) {
            return null;
        }

        if (false === $user instanceof UserInterface) {
            throw new RuntimeException(sprintf(
                'Expected instance of %s but got instance of %s',
                UserInterface::class,
                get_class($user)
            ));
        }

        return $user;
    }

    private function isUserEligibleForResettingPassword(ResettablePasswordInterface $user): bool
    {
        if (true === $user instanceof ActivableInterface && false === $user->isEnabled()) {
            return false;
        }

        return false === $this->hasNonExpiredPasswordResetToken($user);
    }

    private function hasNonExpiredPasswordResetToken(ResettablePasswordInterface $user): bool
    {
        return null !== $user->getPasswordResetToken()
            && true === $user->getPasswordResetToken()->isNonExpired($this->clock)
        ;
    }
}
