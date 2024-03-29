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
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use function is_string;

class ChangePasswordCommand extends Command
{
    use QuestionHelper;

    private UserRepositoryInterface $userRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        UserRepositoryInterface $userRepository,
        EventDispatcherInterface $eventDispatcher,
        ?string $name = null
    ) {
        parent::__construct($name);

        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function configure(): void
    {
        $this
            ->setName('fsi:user:change-password')
            ->setDescription('Change password of a user.')
            ->setDefinition([
                new InputArgument('email', InputArgument::REQUIRED, 'The email'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password'),
            ])
            ->setHelp(<<<EOT
The <info>fsi:user:change-password</info> command changes user's password:

  <info>php app/console fsi:user:change-password john@example.com</info>

This interactive shell will first ask you for a password.

You can alternatively specify the new password as a second argument:

  <info>php app/console fsi:user:change-password john@example.com mynewpassword</info>

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

        $user = $this->userRepository->findUserByEmail($email);
        if (false === $user instanceof ChangeablePasswordInterface) {
            throw new InvalidArgumentException("User with email \"{$email}\" cannot be found");
        }

        $user->setPlainPassword($password);
        $this->eventDispatcher->dispatch(new ChangePasswordEvent($user));

        $output->writeln("Changed password of user <comment>{$email}</comment>");

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
            if ('' === $password) {
                throw new Exception('Password can not be empty');
            }

            return $password;
        });

        $password = $this->getQuestionHelper()->ask($input, $output, $question);
        $input->setArgument('password', $password);
    }
}
