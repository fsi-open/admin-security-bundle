<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\BatchEvents;
use FSi\Bundle\AdminBundle\Event\FormEvent;
use FSi\Bundle\AdminSecurityBundle\Doctrine\Admin\UserElement;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PreventDeletingCurrentUser implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            BatchEvents::BATCH_OBJECTS_PRE_APPLY => 'preventDeletingCurrentUser',
        );
    }

    public function preventDeletingCurrentUser(FormEvent $event)
    {
        $element = $event->getElement();

        if (!($element instanceof UserElement)) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $request = $event->getRequest();
        $indexes = $request->get('indexes', array());

        foreach ($indexes as $index) {
            /** @var \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $entity */
            $entity = $element->getDataIndexer()->getData($index);

            if ($user === $entity) {
                $this->setRedirectResponse($event);

                return;
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    private function setRedirectResponse(FormEvent $event)
    {
        $event->stopPropagation();
        $event->setResponse(new RedirectResponse($event->getRequest()->get('redirect_uri')));
    }
}
