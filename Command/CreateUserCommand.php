<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Command;

use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Event\UserEvent;
use FSi\Bundle\AdminSecurityBundle\Security\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('fsi:user:create')
            ->setDescription('Create a user.')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                new InputArgument('email', InputArgument::REQUIRED, 'The email'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password'),
                new InputArgument('role', InputArgument::REQUIRED, 'Role'),
                new InputOption('inactive', null, InputOption::VALUE_NONE, 'Set the user as inactive'),
                new InputOption('enforce-password-change', null, InputOption::VALUE_NONE, 'Enforce user to change password during next login'),
            ))
            ->setHelp(<<<EOT
The <info>fsi:user:create</info> command creates a user:

  <info>php app/console fsi:user:create john</info>

This interactive shell will ask you for an email and then a password.

You can alternatively specify the email, password and role as the second, third and fourth arguments:

  <info>php app/console fsi:user:create john john@example.com mypassword ROLE_ADMIN</info>

EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $role = $input->getArgument('role');

        $userClass = $this->getContainer()->getParameter('admin_security.model.user');
        /** @var UserInterface $user */
        $user = new $userClass();
        $user->setUserName($username);
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

        $output->writeln(sprintf('Created user <comment>%s</comment>', $username));
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('username')) {
            $username = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a username:',
                function($username) {
                    if (empty($username)) {
                        throw new \Exception('Username can not be empty');
                    }

                    return $username;
                }
            );
            $input->setArgument('username', $username);
        }

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
