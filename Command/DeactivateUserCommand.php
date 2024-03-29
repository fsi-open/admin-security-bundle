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
use FSi\Bundle\AdminSecurityBundle\Event\DeactivationEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use function is_string;

class DeactivateUserCommand extends Command
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
            ->setName('fsi:user:deactivate')
            ->setDescription('Deactivate a user.')
            ->setDefinition([
                new InputArgument('email', InputArgument::REQUIRED, 'The email'),
            ])
            ->setHelp(<<<EOT
The <info>fsi:user:deactivate</info> command deactivates the user

  <info>php app/console fsi:user:deactivate john@example.com</info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        if (false === is_string($email)) {
            throw new InvalidArgumentException('Email is not a string!');
        }

        $user = $this->userRepository->findUserByEmail($email);
        if (false === $user instanceof ActivableInterface) {
            throw new InvalidArgumentException("User with email \"{$email}\" cannot be found");
        }

        $this->eventDispatcher->dispatch(new DeactivationEvent($user));

        $output->writeln("User <comment>{$email}</comment> has been deactivated");

        return 0;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null === $input->getArgument('email')) {
            $this->askEmail($input, $output);
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
}
