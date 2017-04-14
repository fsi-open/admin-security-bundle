<?php

namespace spec\FSi\Bundle\AdminSecurityBundle;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PhpSpec\ObjectBehavior;

class FSiAdminSecurityBundleSpec extends ObjectBehavior
{
    const USER_CLASS = '\FSi\Bundle\AdminSecurityBundle\spec\fixtures\User';
    const CORRECT_REPOSITORY = '\FSi\Bundle\AdminSecurityBundle\spec\fixtures\FSiUserRepository';
    const INCORRECT_REPOSITORY = '\FSi\Bundle\AdminSecurityBundle\spec\fixtures\NonFSiUserRepository';

    function let(
        ContainerInterface $container,
        RegistryInterface $doctrine,
        ObjectManager $manager,
        ClassMetadata $classMetadata
    ) {
        $container->get('doctrine')->willReturn($doctrine);
        $container->getParameter('admin_security.model.user')->willReturn(self::USER_CLASS);
        $doctrine->getManagerForClass(self::USER_CLASS)->willReturn($manager);
        $manager->getClassMetadata(self::USER_CLASS)->willReturn($classMetadata);
    }

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
        ClassMetadata $classMetadata
    ) {
        $this->setContainer($container);
        $classMetadata->customRepositoryClassName = self::CORRECT_REPOSITORY;

        $this->boot();
    }

    function it_throws_exception_when_incorrect_user_model_repository_class(
        ContainerInterface $container,
        ClassMetadata $classMetadata
    ) {
        $this->setContainer($container);
        $classMetadata->customRepositoryClassName = self::INCORRECT_REPOSITORY;

        $this->shouldThrow('\LogicException')->during('boot');
    }
}
