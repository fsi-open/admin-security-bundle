<?php

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\BatchEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PreventDeletingCurrentUserSpec extends ObjectBehavior
{
    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \FSi\Bundle\AdminBundle\Event\FormEvent $event
     * @param \FSi\Bundle\AdminSecurityBundle\Doctrine\Admin\UserElement $userElement
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \FSi\Component\DataIndexer\DataIndexerInterface $dataIndexer
     * @param \FSi\Bundle\AdminBundle\Message\FlashMessages $flashMessages
     */
    function let($tokenStorage, $router, $event, $userElement, $token, $request, $dataIndexer, $flashMessages) {
        $event->getElement()->willReturn($userElement);
        $tokenStorage->getToken()->willReturn($token);
        $event->getRequest()->willReturn($request);
        $request->get('redirect_uri')->willReturn('list_url');
        $userElement->getDataIndexer()->willReturn($dataIndexer);

        $this->beConstructedWith($tokenStorage, $router, $flashMessages);
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
     * @param \FSi\Bundle\AdminBundle\Message\FlashMessages $flashMessages
     */
    function it_should_prevent_deleting_current_user($event, $request, $token, $user, $dataIndexer, $flashMessages)
    {
        $token->getUser()->willReturn($user);
        $request->get('indexes', array())->willReturn(array(1));
        $dataIndexer->getData(1)->willReturn($user);

        $event->stopPropagation()->shouldBeCalled();
        $event->setResponse(Argument::allOf(
            Argument::type('Symfony\Component\HttpFoundation\RedirectResponse'),
            Argument::which('getTargetUrl', 'list_url')
        ))->shouldBeCalled();
        $flashMessages->error(Argument::cetera())->shouldBeCalled();

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

    /**
     * @param \FSi\Bundle\AdminBundle\Event\FormEvent $event
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param \FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface $user
     * @param \FSi\Component\DataIndexer\DataIndexerInterface $dataIndexer
     * @param \FSi\Bundle\AdminSecurityBundle\Doctrine\Admin\UserElement $userElement
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    function it_should_redirect_to_element_when_there_is_no_redirect_uri($event, $request, $token, $user, $dataIndexer, $userElement, $router)
    {
        $token->getUser()->willReturn($user);
        $request->get('indexes', array())->willReturn(array(1));
        $dataIndexer->getData(1)->willReturn($user);

        $request->get('redirect_uri')->willReturn(null);

        $event->getElement()->willReturn($userElement);
        $userElement->getRoute()->willReturn('route_name');
        $userElement->getRouteParameters()->willReturn(array(1, 2, 3));
        $router->generate('route_name', array(1, 2, 3))->willReturn('http://example.com/1,2,3');

        $event->stopPropagation()->shouldBeCalled();
        $event->setResponse(Argument::allOf(
            Argument::type('Symfony\Component\HttpFoundation\RedirectResponse'),
            Argument::which('getTargetUrl', 'http://example.com/1,2,3')
        ))->shouldBeCalled();

        $this->preventDeletingCurrentUser($event);
    }
}
