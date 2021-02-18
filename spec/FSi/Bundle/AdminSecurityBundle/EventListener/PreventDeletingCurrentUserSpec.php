<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Event\BatchEvents;
use FSi\Bundle\AdminBundle\Event\FormEvent;
use FSi\Bundle\AdminBundle\Message\FlashMessages;
use FSi\Bundle\AdminSecurityBundle\Doctrine\Admin\UserElement;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Component\DataIndexer\DataIndexerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PreventDeletingCurrentUserSpec extends ObjectBehavior
{
    public function let(
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        FormEvent $event,
        UserElement $userElement,
        AbstractToken $token,
        Request $request,
        DataIndexerInterface $dataIndexer,
        FlashMessages $flashMessages
    ): void {
        $event->getElement()->willReturn($userElement);
        $tokenStorage->getToken()->willReturn($token);
        $event->getRequest()->willReturn($request);
        $request->get('redirect_uri')->willReturn('list_url');
        $userElement->getDataIndexer()->willReturn($dataIndexer);

        $this->beConstructedWith($tokenStorage, $router, $flashMessages);
    }

    public function it_is_event_subscriber(): void
    {
        $this->shouldHaveType(EventSubscriberInterface::class);
    }

    public function it_should_subscribe_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            BatchEvents::BATCH_OBJECTS_PRE_APPLY => 'preventDeletingCurrentUser',
        ]);
    }

    public function it_should_prevent_deleting_current_user(
        FormEvent $event,
        AbstractToken $token,
        Request $request,
        DataIndexerInterface $dataIndexer,
        FlashMessages $flashMessages,
        UserInterface $user
    ): void {
        $token->getUser()->willReturn($user);
        $request->get('indexes', [])->willReturn(['1']);
        $dataIndexer->getData('1')->willReturn($user);

        $event->stopPropagation()->shouldBeCalled();
        $event->setResponse(
            Argument::allOf(
                Argument::type(RedirectResponse::class),
                Argument::which('getTargetUrl', 'list_url')
            )
        )->shouldBeCalled();
        $flashMessages->error(Argument::cetera())->shouldBeCalled();

        $this->preventDeletingCurrentUser($event);
    }

    public function it_should_allow_deleting_other_users(
        FormEvent $event,
        AbstractToken $token,
        Request $request,
        DataIndexerInterface $dataIndexer,
        UserInterface $user,
        UserInterface $currentUser
    ): void {
        $token->getUser()->willReturn($currentUser);
        $request->get('indexes', [])->willReturn(['1']);
        $dataIndexer->getData('1')->willReturn($user);

        $event->stopPropagation()->shouldNotBeCalled();
        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->preventDeletingCurrentUser($event);
    }

    public function it_should_redirect_to_element_when_there_is_no_redirect_uri(
        RouterInterface $router,
        FormEvent $event,
        UserElement $userElement,
        AbstractToken $token,
        Request $request,
        DataIndexerInterface $dataIndexer,
        UserInterface $user
    ): void {
        $token->getUser()->willReturn($user);
        $request->get('indexes', [])->willReturn(['1']);
        $dataIndexer->getData('1')->willReturn($user);

        $request->get('redirect_uri')->willReturn(null);

        $event->getElement()->willReturn($userElement);
        $userElement->getRoute()->willReturn('route_name');
        $userElement->getRouteParameters()->willReturn([1, 2, 3]);
        $router->generate('route_name', [1, 2, 3])->willReturn('http://example.com/1,2,3');

        $event->stopPropagation()->shouldBeCalled();
        $event->setResponse(
            Argument::allOf(
                Argument::type(RedirectResponse::class),
                Argument::which('getTargetUrl', 'http://example.com/1,2,3')
            )
        )->shouldBeCalled();

        $this->preventDeletingCurrentUser($event);
    }
}
