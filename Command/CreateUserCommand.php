<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Command;

use Exception;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use function is_string;

class CreateUserCommand extends Command
{
    private EventDispatcherInterface $eventDispatcher;
    /**
     * @var class-string<UserInterface>
     */
    private string $userClass;

    /**
     * @param class-string<UserInterface> $userClass
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        string $userClass,
        ?string $name = null
    ) {
        parent::__construct($name);

        $this->eventDispatcher = $eventDispatcher;
        $this->userClass = $userClass;
    }

    protected function configure(): void
    {
        $this
            ->setName('fsi:user:create')
            ->setDescription('Create a user.')
            ->setDefinition([
                new InputArgument('email', InputArgument::REQUIRED, 'The email'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password'),
                new InputArgument('role', InputArgument::REQUIRED, 'Role'),
                new InputOption('inactive', null, InputOption::VALUE_NONE, 'Set the user as inactive'),
                new InputOption(
                    'enforce-password-change',
                    null,
                    InputOption::VALUE_NONE,
                    'Enforce user to change password during next login'
                ),
            ])
            ->setHelp(<<<EOT
The <info>fsi:user:create</info> command creates a user:

  <info>php app/console fsi:user:create</info>

This interactive shell will ask you for an email and then a password.

You can alternatively specify the email, password and role as the first, second and third arguments:

  <info>php app/console fsi:user:create john@example.com mypassword ROLE_ADMIN</info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        if (false === is_string($email)) {
            throw new InvalidArgumentException('Email is not a string!');
        }

        $password = $input->getArgument('password');
        if (false === is_string($password)) {
            throw new InvalidArgumentException('Password is not a string!');
        }

        $role = $input->getArgument('role');
        if (false === is_string($role)) {
            throw new InvalidArgumentException('Role is not a string!');
        }

        $user = new $this->userClass();
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->addRole($role);
        if (false === $input->getOption('inactive')) {
            $user->setEnabled(true);
        }
        if (true === $input->getOption('enforce-password-change')) {
            $user->enforcePasswordChange(true);
        }
        $this->eventDispatcher->dispatch(new UserEvent($user), AdminSecurityEvents::USER_CREATED);

        $output->writeln(sprintf('Created user <comment>%s</comment>', $email));

        return 0;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null === $input->getArgument('email')) {
            $this->askEmail($input, $output);
        }

        if (null === $input->getArgument('password')) {
            $this->askPassword($input, $output);
        }

        if (null === $input->getArgument('role')) {
            $this->askRole($input, $output);
        }
    }

    private function askEmail(InputInterface $input, OutputInterface $output): void
    {
        $question = new Question('Please choose an email:');
        $question->setValidator(function (string $email): string {
            if ('' === $email) {
                throw new Exception('Email can not be empty');
            }

            return $email;
        });

        $email = $this->getQuestionHelper()->ask($input, $output, $question);
        $input->setArgument('email', $email);
    }

    private function askPassword(InputInterface $input, OutputInterface $output): void
    {
        $question = new Question('Please choose a password:');
        $question->setValidator(function (string $password): string {
            if (empty($password)) {
                throw new Exception('Password can not be empty');
            }

            return $password;
        });

        $password = $this->getQuestionHelper()->ask($input, $output, $question);
        $input->setArgument('password', $password);
    }

    private function askRole(InputInterface $input, OutputInterface $output): void
    {
        $question = new Question('Please choose a role:');
        $question->setValidator(function (string $password): string {
            if ('' === $password) {
                throw new Exception('Role can not be empty');
            }

            return $password;
        });

        $role = $this->getQuestionHelper()->ask($input, $output, $question);
        $input->setArgument('role', $role);
    }

    private function getQuestionHelper(): QuestionHelper
    {
        return $this->getHelper('question');
    }
}
