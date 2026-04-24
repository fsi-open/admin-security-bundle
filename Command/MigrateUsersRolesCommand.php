<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Command;

use Assert\Assertion;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function implode;
use function is_array;
use function json_decode;
use function sprintf;
use function unserialize;

class MigrateUsersRolesCommand extends Command
{
    private ManagerRegistry $managerRegistry;
    /**
     * @var class-string<UserInterface>
     */
    private string $userClass;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param class-string<UserInterface> $userClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        string $userClass
    ) {
        parent::__construct();

        $this->managerRegistry = $managerRegistry;
        $this->userClass = $userClass;
    }

    protected function configure(): void
    {
        $this
            ->setName('fsi:user:migrate-roles')
            ->setDescription('Migrate users\' roles from \'array\' to \'json\' data type.')
            ->addArgument(
                'fieldName',
                InputArgument::OPTIONAL,
                'Name of the field which contains serialized user\'s roles',
                'roles'
            )
            ->setHelp(<<<EOT
The <info>fsi:user:migrate-roles</info> command migrates all users' roles from \'array\' data type used
in fsi-open/admin-security-bundle < 5.0 to \'json\' data type used in 5.0 version.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rolesFieldName = $input->getArgument('fieldName');
        Assertion::string($rolesFieldName);

        $manager = $this->managerRegistry->getManagerForClass($this->userClass);
        Assertion::notNull($manager, "No manager found for class '{$this->userClass}'.");
        Assertion::isInstanceOf($manager, EntityManagerInterface::class);

        $userMetadata = $manager->getClassMetadata($this->userClass);
        Assertion::true(
            $userMetadata->hasField($rolesFieldName),
            "The class '{$this->userClass}' does not have ORM mapped field '$rolesFieldName'."
        );

        Assertion::same($userMetadata->getTypeOfField($rolesFieldName), Types::JSON);

        $queryBuilder = $manager->getConnection()
            ->createQueryBuilder()
            ->select(...$userMetadata->getIdentifierFieldNames())
            ->addSelect($rolesFieldName)
            ->from($userMetadata->getTableName(), 'u');
        $result = $queryBuilder->executeQuery()->fetchAllAssociative();
        foreach ($result as $row) {
            $rolesSerialized = end($row);
            unset($row[$rolesFieldName]);
            $roles = @unserialize($rolesSerialized, ['allowed_classes' => []]);
            if (false === is_array($roles)) {
                if (false === is_array(json_decode($rolesSerialized, true, 512, JSON_THROW_ON_ERROR))) {
                    $output->writeln(
                        sprintf(
                            "<warning>Unable to unserialize roles of user '%s' into PHP array.</warning>",
                            implode('-', $row)
                        )
                    );
                } else {
                    $output->writeln(
                        sprintf(
                            "<info>User '%s' has already roles encoded as JSON.</info>",
                            implode('-', $row)
                        )
                    );
                }
            } else {
                $manager->getConnection()->update(
                    $userMetadata->getTableName(),
                    [$rolesFieldName => json_encode($roles, JSON_THROW_ON_ERROR)],
                    $row
                );

                $output->writeln(
                    sprintf(
                        "<info>Successfully converted user's '%s' roles from a serialized array to JSON.</info>",
                        implode('-', $row)
                    )
                );
            }
        }

        return 0;
    }
}
