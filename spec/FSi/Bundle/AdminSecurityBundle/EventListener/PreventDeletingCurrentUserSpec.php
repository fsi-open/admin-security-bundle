<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\BatchEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PreventDeletingCurrentUserSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \FSi\Bundle\AdminBundle\Event\FormEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Doctrine\Admin\UserElement $userElement
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Component\DataIndexer\DataIndexerInterface $dataIndexer
     */
    function let($tokenStorage, $event, $userElement, $token, $request, $dataIndexer) {
        $event->getElement()->willReturn($userElement);
        $tokenStorage->getToken()->willReturn($token);
        $event->getRequest()->willReturn($request);
        $request->get('redirect_uri')->willReturn('list_url');
        $userElement->getDataIndexer()->willReturn($dataIndexer);

        $this->beConstructedWith($tokenStorage);
    }

    function it_is_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_should_subscribe_events()
    {
        $this->getSubscribedEvents()->shouldReturn(array(
            BatchEvents::BATCH_OBJECTS_PRE_APPLY => 'preventDeletingCurrentUser'
        ));
    }

    /**
     * @param \FSi\Bundle\AdminBundle\Event\FormEvent $event
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     * @param \FSi\Component\DataIndexer\DataIndexerInterface $dataIndexer
     */
    function it_should_prevent_deleting_current_user($event, $request, $token, $user, $dataIndexer)
    {
        $token->getUser()->willReturn($user);
        $request->get('indexes', array())->willReturn(array(1));
        $dataIndexer->getData(1)->willReturn($user);

        $event->stopPropagation()->shouldBeCalled();
        $event->setResponse(Argument::allOf(
            Argument::type('Symfony\Component\HttpFoundation\RedirectResponse'),
            Argument::which('getTargetUrl', 'list_url')
        ))->shouldBeCalled();

        $this->preventDeletingCurrentUser($event);
    }

    /**
     * @param \FSi\Bundle\AdminBundle\Event\FormEvent $event
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $currentUser
     * @param \FSi\Component\DataIndexer\DataIndexerInterface $dataIndexer
     */
    function it_should_allow_deleting_other_users($event, $request, $token, $user, $currentUser, $dataIndexer)
    {
        $token->getUser()->willReturn($currentUser);
        $request->get('indexes', array())->willReturn(array(1));
        $dataIndexer->getData(1)->willReturn($user);

        $event->stopPropagation()->shouldNotBeCalled();
        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->preventDeletingCurrentUser($event);
    }
}
