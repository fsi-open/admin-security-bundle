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
use FSi\Bundle\AdminSecurityBundle\Event\DemoteUserEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class DemoteUserCommand extends Command
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
            ->setName('fsi:user:demote')
            ->setDescription('Demote a user.')
            ->setDefinition([
                new InputArgument('email', InputArgument::REQUIRED, 'The email'),
                new InputArgument('role', InputArgument::REQUIRED, 'The role'),
            ])
            ->setHelp(<<<EOT
The <info>fsi:user:demote</info> command demotes the user

  <info>php app/console fsi:user:demote john@example.com ROLE_ADMIN</info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        if (false === is_string($email)) {
            throw new InvalidArgumentException('Email is not a string!');
        }

        $role = $input->getArgument('role');
        if (false === is_string($role)) {
            throw new InvalidArgumentException('Role is not a string!');
        }

        $user = $this->userRepository->findUserByEmail($email);
        if (false === $user instanceof UserInterface) {
            throw new InvalidArgumentException("User with email \"{$email}\" cannot be found");
        }

        $user->removeRole($role);
        $this->eventDispatcher->dispatch(new DemoteUserEvent($user, $role));

        $output->writeln("User <comment>{$email}</comment> has been demoted");

        return 0;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null === $input->getArgument('email')) {
            $this->askEmail($input, $output);
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
}
