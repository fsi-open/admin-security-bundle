<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle\Controller;

use FSi\Bundle\AdminBundle\Message\FlashMessages;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityControllerSpec extends ObjectBehavior
{
    function let(
        EngineInterface $templating,
        AuthenticationUtils $authenticationUtils,
        FlashMessages $flashMessages
    ) {
        $this->beConstructedWith(
            $templating,
            $authenticationUtils,
            $flashMessages,
            'login_template'
        );
    }

    function it_render_login_template_in_login_action(
        EngineInterface $templating,
        AuthenticationUtils $authenticationUtils,
        FlashMessages $flashMessages,
        AuthenticationException $exception,
        Response $response
    ) {
        $error = new \Exception('message');
        $authenticationUtils->getLastAuthenticationError()->willReturn($error);
        $exception->getMessageKey()->willReturn('error');
        $exception->getMessageData()->willReturn(['parameter' => 'value']);
        $authenticationUtils->getLastAuthenticationError()->willReturn($exception);
        $authenticationUtils->getLastUsername()->willReturn('user');

        $flashMessages->error(
            Argument::type('string'),
            Argument::type('array'),
            Argument::type('string')
        )->shouldBeCalled();

        $templating->renderResponse(
            Argument::type('string'),
            ['last_username' => 'user']
        )->willReturn($response);

        $this->loginAction()->shouldReturn($response);
    }
}
