<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\FSi\Bundle\AdminSecurityBundle;

use FSi\Bundle\AdminSecurityBundle\Doctrine\Repository\UserRepository;
use FSi\Bundle\AdminSecurityBundle\spec\fixtures\NonFSiUserRepository;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FSiAdminSecurityBundleSpec extends ObjectBehavior
{
    function it_is_bundle()
    {
        $this->shouldHaveType('Symfony\Component\HttpKernel\Bundle\Bundle');
    }

    function it_has_custom_extension()
    {
        $this->getContainerExtension()->shouldReturnAnInstanceOf(
            'FSi\Bundle\AdminSecurityBundle\DependencyInjection\FSIAdminSecurityExtension'
        );
    }

    function it_does_not_throw_exception_when_correct_user_model_repository_class(
        ContainerInterface $container,
        UserRepository $correctRepository
    ) {
        $container->get('admin_security.repository.user')->willReturn($correctRepository);
        $this->setContainer($container);

        $this->boot();
    }

    function it_throws_exception_when_incorrect_user_model_repository_class(
        ContainerInterface $container,
        NonFSiUserRepository $incorrectRepository
    ) {
        $container->get('admin_security.repository.user')->willReturn($incorrectRepository);
        $this->setContainer($container);

        $this->shouldThrow('\LogicException')->during('boot');
    }
}
