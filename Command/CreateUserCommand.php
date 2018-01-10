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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends ContainerAwareCommand
{
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
                new InputOption('enforce-password-change', null, InputOption::VALUE_NONE, 'Enforce user to change password during next login'),
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

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $role = $input->getArgument('role');

        $userClass = $this->getContainer()->getParameter('admin_security.model.user');
        /** @var UserInterface $user */
        $user = new $userClass();
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->addRole($role);
        if (!$input->getOption('inactive')) {
            $user->setEnabled(true);
        }
        if ($input->getOption('enforce-password-change')) {
            $user->enforcePasswordChange(true);
        }

        $this->getContainer()->get('event_dispatcher')->dispatch(
            AdminSecurityEvents::USER_CREATED,
            new UserEvent($user)
        );

        $output->writeln(sprintf('Created user <comment>%s</comment>', $email));
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

        if (!$input->getArgument('password')) {
            $password = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a password:',
                function($password) {
                    if (empty($password)) {
                        throw new \Exception('Password can not be empty');
                    }

                    return $password;
                }
            );
            $input->setArgument('password', $password);
        }

        if (!$input->getArgument('role')) {
            $role = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a role:',
                function($role) {
                    if (empty($role)) {
                        throw new \Exception('Role can not be empty');
                    }

                    return $role;
                }
            );
            $input->setArgument('role', $role);
        }
    }
}
