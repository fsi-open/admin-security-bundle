<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Admin\Element;
use FSi\Bundle\AdminBundle\Event\BatchEvents;
use FSi\Bundle\AdminBundle\Event\FormEvent;
use FSi\Bundle\AdminBundle\Message\FlashMessages;
use FSi\Bundle\AdminSecurityBundle\Doctrine\Admin\UserElement;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PreventDeletingCurrentUser implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FlashMessages
     */
    private $flashMessages;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        FlashMessages $flashMessages
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->flashMessages = $flashMessages;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BatchEvents::BATCH_OBJECTS_PRE_APPLY => 'preventDeletingCurrentUser',
        ];
    }

    public function preventDeletingCurrentUser(FormEvent $event): void
    {
        $element = $event->getElement();

        if (!($element instanceof UserElement)) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $request = $event->getRequest();
        $indexes = $request->get('indexes', []);

        foreach ($indexes as $index) {
            /** @var \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $entity */
            $entity = $element->getDataIndexer()->getData($index);

            if ($user === $entity) {
                $this->setRedirectResponse($event);
                $this->flashMessages->error(
                    'admin.user_list.message.delete_current_user',
                    [],
                    'FSiAdminSecurity'
                );

                return;
            }
        }
    }

    private function setRedirectResponse(FormEvent $event): void
    {
        $event->stopPropagation();
        $redirectUrl = $this->getRedirectUrl($event->getElement(), $event->getRequest());
        $event->setResponse(new RedirectResponse($redirectUrl));
    }

    private function getRedirectUrl(Element $element, Request $request): string
    {
        $redirectUrl = $request->get('redirect_uri');

        if ($redirectUrl === null) {
            return $this->router->generate($element->getRoute(), $element->getRouteParameters());
        }

        return $redirectUrl;
    }
}
