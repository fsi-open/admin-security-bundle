<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Mink\Session;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use FSi\Bundle\AdminSecurityBundle\Command\CreateUserCommand;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class CommandContext extends AbstractContext
{
    private KernelInterface $kernel;
    private ?Application $application;
    private ?CommandTester $tester;

    public function __construct(
        Session $session,
        MinkParameters $minkParameters,
        EntityManagerInterface $entityManager,
        KernelInterface $kernel
    ) {
        parent::__construct($session, $minkParameters, $entityManager);
        $this->kernel = $kernel;
        $this->application = null;
        $this->tester = null;
    }

    /**
     * @Given I create disabled user with email address :email and enforced password change
     */
    public function iCreateDisabledUserWithEmailAddressAndEnforcedPasswordChange(string $email): void
    {
        $this->runCommand(
            'fsi:user:create',
            [
                'command' => 'fsi:user:create',
                'email' => $email,
                'password' => 'admin',
                'role' => 'ROLE_ADMIN',
                '--inactive' => true,
                '--enforce-password-change' => true
            ]
        );
    }

    /**
     * @Given I create disabled user with email address :email
     */
    public function iCreateDisabledUserWithEmailAddress(string $email): void
    {
        $this->runCommand(
            'fsi:user:create',
            [
                'command' => 'fsi:user:create',
                'email' => $email,
                'password' => 'admin',
                'role' => 'ROLE_ADMIN',
                '--inactive' => true
            ]
        );
    }

    private function getApplication(): Application
    {
        if (null === $this->application) {
            $container = $this->kernel->getContainer();

            /** @var EventDispatcherInterface $eventDispatcher */
            $eventDispatcher = $container->get('event_dispatcher');

            /** @var string|class-string<UserInterface>|int|null $userClass */
            $userClass = $container->getParameter('admin_security.model.user');
            if (false === is_string($userClass)) {
                throw new Exception('"admin_security.model.user" parameter is not a string!');
            }

            if (false === is_subclass_of($userClass, UserInterface::class)) {
                throw new Exception(sprintf(
                    '"admin_security.model.user" parameter is not a class string implementing "%s"!',
                    UserInterface::class
                ));
            }

            $this->application = new Application($this->kernel);
            $this->application->add(new CreateUserCommand($eventDispatcher, $userClass));
        }

        return $this->application;
    }

    /**
     * @param array<string, string|bool|null> $arguments
     * @return void
     */
    private function runCommand(string $name, array $arguments): void
    {
        if (null === $this->tester) {
            $command = $this->getApplication()->find($name);
            $this->tester = new CommandTester($command);
        }

        $this->tester->execute($arguments);
    }
}
