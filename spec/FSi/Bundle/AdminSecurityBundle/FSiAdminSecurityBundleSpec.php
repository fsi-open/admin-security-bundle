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
use LogicException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use FSi\Bundle\AdminSecurityBundle\DependencyInjection\FSIAdminSecurityExtension;

class FSiAdminSecurityBundleSpec extends ObjectBehavior
{
    public function it_is_bundle(): void
    {
        $this->shouldHaveType(Bundle::class);
    }

    public function it_has_custom_extension(): void
    {
        $this->getContainerExtension()->shouldReturnAnInstanceOf(FSIAdminSecurityExtension::class);
    }

    public function it_does_not_throw_exception_when_correct_user_model_repository_class(
        ContainerInterface $container,
        UserRepository $correctRepository
    ): void {
        $container->get('admin_security.repository.user')->willReturn($correctRepository);
        $this->setContainer($container);

        $this->boot();
    }

    public function it_throws_exception_when_incorrect_user_model_repository_class(
        ContainerInterface $container,
        NonFSiUserRepository $incorrectRepository
    ): void {
        $container->get('admin_security.repository.user')->willReturn($incorrectRepository);
        $this->setContainer($container);

        $this->shouldThrow(LogicException::class)->during('boot');
    }
}
