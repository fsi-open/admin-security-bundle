<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use FSi\Bundle\AdminSecurityBundle\Command\CreateUserCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class CommandContext implements KernelAwareContext
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
        $this->application = new Application($kernel);
        $this->application->add(new CreateUserCommand());
    }

    /**
     * @When /^I run a "([^"]*)" command$/
     */
    public function iRunCommand($name)
    {
        $command = $this->application->find($name);
        $this->tester = new CommandTester($command);
        $this->tester->execute(['command' => $command->getName()]);
    }

    /**
     * @Given /^I run a command "([^"]*)" with parameters:$/
     */
    public function iRunCommandWithParameters($command, TableNode $parameters)
    {
        $params = [];
        foreach ($parameters->getHash() as $parameterRow) {
            $params[$parameterRow['parameter']] = $parameterRow['value'];
        }

        $command = $this->application->find($command);
        $this->tester = new CommandTester($command);
        $this->tester->execute(['command' => $command->getName()]);
    }

    /**
     * @Given I create disabled user with email address :email and enforced password change
     */
    public function iCreateDisabledUserWithEmailAddressAndEnforcedPasswordChange($email)
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
    public function iCreateDisabledUserWithEmailAddress($email)
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
