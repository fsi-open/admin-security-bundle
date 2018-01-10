<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Command;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PromoteUserCommand extends ContainerAwareCommand
{
    protected function configure(): void
    {
        $this
            ->setName('fsi:user:promote')
            ->setDescription('Promote a user.')
            ->setDefinition([
                new InputArgument('email', InputArgument::REQUIRED, 'The email'),
                new InputArgument('role', InputArgument::REQUIRED, 'The role'),
            ])
            ->setHelp(<<<EOT
The <info>fsi:user:promote</info> command promotes the user

  <info>php app/console fsi:user:promote john@example.com ROLE_ADMIN</info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $email = $input->getArgument('email');
        $role = $input->getArgument('role');

        /** @var UserRepositoryInterface $userRepository */
        $userRepository = $this->getContainer()->get('admin_security.repository.user');
        $user = $userRepository->findUserByEmail($email);
        if (!($user instanceof UserInterface)) {
            throw new \InvalidArgumentException(sprintf('User with email "%s" cannot be found', $email));
        }

        $user->addRole($role);

        $this->getContainer()->get('event_dispatcher')->dispatch(
            AdminSecurityEvents::PROMOTE_USER,
            new UserEvent($user)
        );

        $output->writeln(sprintf('User <comment>%s</comment> has been promoted', $email));
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (!$input->getArgument('email')) {
            $email = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose an email:',
                function($email) {
                    if (empty($email)) {
                        throw new \Exception('Email can not be empty');
                    }

                    return $email;
                }
            );
            $input->setArgument('email', $email);
        }

        if (!$input->getArgument('role')) {
            $email = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a role:',
                function($email) {
                    if (empty($email)) {
                        throw new \Exception('Role can not be empty');
                    }

                    return $email;
                }
            );
            $input->setArgument('role', $email);
        }
    }
}
