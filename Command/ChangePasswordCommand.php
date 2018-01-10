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
use FSi\Bundle\AdminSecurityBundle\Event\ChangePasswordEvent;
use FSi\Bundle\AdminSecurityBundle\Security\User\ChangeablePasswordInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChangePasswordCommand extends ContainerAwareCommand
{
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

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $userRepository = $this->getContainer()->get('admin_security.repository.user');
        $user = $userRepository->findUserByEmail($email);
        if (!($user instanceof ChangeablePasswordInterface)) {
            throw new \InvalidArgumentException(sprintf('User with email "%s" cannot be found', $email));
        }
        $user->setPlainPassword($password);

        $this->getContainer()->get('event_dispatcher')->dispatch(
            AdminSecurityEvents::CHANGE_PASSWORD,
            new ChangePasswordEvent($user)
        );

        $output->writeln(sprintf('Changed password of user <comment>%s</comment>', $email));
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
    }
}
