<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Symfony2Extension\Context\KernelAwareContext;
use FSi\Bundle\AdminSecurityBundle\Command\CreateUserCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class CommandContext implements KernelAwareContext
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var CommandTester
     */
    private $tester;

    public function setKernel(KernelInterface $kernel): void
    {
        $this->kernel = $kernel;
        $container = $kernel->getContainer();
        $this->application = new Application($kernel);
        $this->application->add(
            new CreateUserCommand(
                $container->get('event_dispatcher'),
                $container->getParameter('admin_security.model.user')
            )
        );
    }

    /**
     * @Given I create disabled user with email address :email and enforced password change
     */
    public function iCreateDisabledUserWithEmailAddressAndEnforcedPasswordChange(string $email): void
    {
        $command = $this->application->find('fsi:user:create');

        $this->tester = new CommandTester($command);
        $this->tester->execute([
            'command' => $command->getName(),
            'email' => $email,
            'password' => 'admin',
            'role' => 'ROLE_ADMIN',
            '--inactive' => true,
            '--enforce-password-change' => true
        ]);
    }

    /**
     * @Given I create disabled user with email address :email
     */
    public function iCreateDisabledUserWithEmailAddress(string $email): void
    {
        $command = $this->application->find('fsi:user:create');

        $this->tester = new CommandTester($command);
        $this->tester->execute([
            'command' => $command->getName(),
            'email' => $email,
            'password' => 'admin',
            'role' => 'ROLE_ADMIN',
            '--inactive' => true
        ]);
    }
}
