<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Command;

use FSi\Bundle\AdminSecurityBundle\Event\ActivationEvent;
use FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents;
use FSi\Bundle\AdminSecurityBundle\Security\User\ActivableInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeactivateUserCommand extends ContainerAwareCommand
{
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

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $email = $input->getArgument('email');

        $userRepository = $this->getContainer()->get('admin_security.repository.user');
        $user = $userRepository->findUserByEmail($email);
        if (!($user instanceof ActivableInterface)) {
            throw new \InvalidArgumentException(sprintf('User with email "%s" cannot be found', $email));
        }

        $this->getContainer()->get('event_dispatcher')->dispatch(
            AdminSecurityEvents::DEACTIVATION,
            new ActivationEvent($user)
        );

        $output->writeln(sprintf('User <comment>%s</comment> has been activated', $email));
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
    }
}
