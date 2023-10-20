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
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ActivateController
{
    use ActivableUser;
    use FlashWithRedirect;

    private UserRepositoryInterface $userRepository;
    private ClockInterface $clock;
    private EventDispatcherInterface $eventDispatcher;
    private UrlGeneratorInterface $urlGenerator;
    private FlashMessages $flashMessages;

    public function __construct(
        UserRepositoryInterface $userRepository,
        ClockInterface $clock,
        EventDispatcherInterface $eventDispatcher,
        UrlGeneratorInterface $urlGenerator,
        FlashMessages $flashMessages
    ) {
        $this->userRepository = $userRepository;
        $this->clock = $clock;
        $this->eventDispatcher = $eventDispatcher;
        $this->urlGenerator = $urlGenerator;
        $this->flashMessages = $flashMessages;
    }

    public function __invoke(string $token): Response
    {
        $user = $this->tryFindUserByActivationToken($token);
        if (true === $this->isUserEnforcedToChangePassword($user)) {
            $response = $this->addFlashAndRedirect(
                'info',
                'admin.activation.message.change_password',
                'fsi_admin_activation_change_password',
                ['token' => $token]
            );
        } else {
            $this->eventDispatcher->dispatch(new ActivationEvent($user));
            $response = $this->addFlashAndRedirect('success', 'admin.activation.message.success');
        }

        return $response;
    }
}
