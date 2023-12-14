<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Assert\Assertion;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Session;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface;
use FSi\FixturesBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

use function count;
use function interface_exists;

final class DataContext extends AbstractContext
{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container,
        Session $session,
        MinkParameters $minkParameters,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($session, $minkParameters, $entityManager);
        $this->container = $container;
    }

    /**
     * @BeforeScenario
     */
    public function createDatabase(): void
    {
        $this->deleteDatabaseIfExists();

        $manager = $this->getEntityManager();
        $metadata = $manager->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($manager);
        $tool->createSchema($metadata);
    }

    public function deleteDatabaseIfExists(): void
    {
        $manager = $this->getEntityManager();
        $metadata = $manager->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($manager);
        if (0 === count($tool->getDropSchemaSQL($metadata))) {
            return;
        }

        $tool->dropSchema($metadata);
    }

    /**
     * @Given /^there is "([^"]*)" user with role "([^"]*)" and password "([^"]*)"$/
     */
    public function thereIsUserWithRoleAndPassword(string $email, string $role, string $password): void
    {
        $user = new User();
        $user->setUsername($email);
        $user->setEmail($email);
        $user->addRole($role);
        $user->setPlainPassword($password);
        $user->setEnabled(true);

        if (0 !== strlen($password)) {
            $user->setPassword($this->encodeUserPassword($user));
            $user->eraseCredentials();
        }

        $manager = $this->getEntityManager();
        $manager->persist($user);
        $manager->flush();
    }

    /**
     * @Given there are following users:
     */
    public function thereAreFollowingUsers(TableNode $table): void
    {
        $manager = $this->getEntityManager();

        foreach ($table->getHash() as $userInfo) {
            $user = new User();
            $user->setUsername($userInfo['Email']);
            $user->setEmail($userInfo['Email']);
            $user->addRole($userInfo['Role']);
            $user->setPassword('temp password');
            $user->setEnabled(true);

            $manager->persist($user);
        }

        $manager->flush();
    }

    /**
     * @phpcs:disable
     * @Given /^there is "([^"]*)" user with role "([^"]*)" and password "([^"]*)" which is enforced to change password$/
     * @phpcs:enable
     */
    public function thereIsUserWithRoleAndPasswordWhichIsEnforcedToChangePassword(
        string $username,
        string $role,
        string $password
    ): void {
        $this->thereIsUserWithRoleAndPassword($username, $role, $password);

        $user = $this->findUserByUsername($username);
        $user->enforcePasswordChange(true);
        $this->getEntityManager()->flush();
    }

    /**
     * @Then /^user "([^"]*)" has been disabled$/
     */
    public function userHasBeenDisabled(string $userEmail): void
    {
        $user = $this->findUserByEmail($userEmail);
        $user->setEnabled(false);
        $this->getEntityManager()->flush();
    }

    /**
     * @Then /^user "([^"]*)" password should be changed$/
     */
    public function userShouldHaveChangedPassword(string $userEmail): void
    {
        $user = $this->findUserByEmail($userEmail);
        $password = $user->getPassword();
        Assertion::notNull($password, "User \"{$userEmail}\" has no password.");
        Assertion::true(
            $this->verifyUserPassword($user, 'admin-new'),
            'User password has not been changed.'
        );
    }

    /**
     * @Then /^user "([^"]*)" should be enabled$/
     */
    public function userShouldBeEnabled(string $userEmail): void
    {
        $user = $this->findUserByEmail($userEmail);

        Assertion::true($user->isEnabled(), "User \"{$userEmail}\" is not enabled.");
    }

    private function findUserByUsername(string $userName): User
    {
        $user = $this->getRepository(User::class)->findOneBy(['username' => $userName]);
        Assertion::notNull($user, "No user for username \"{$userName}\".");

        $this->getEntityManager()->refresh($user);
        return $user;
    }

    private function findUserByEmail(string $userEmail): User
    {
        $user = $this->getRepository(User::class)->findOneBy(['email' => $userEmail]);
        Assertion::notNull($user, "No user for email \"{$userEmail}\".");

        $this->getEntityManager()->refresh($user);
        return $user;
    }

    private function encodeUserPassword(UserInterface $user): string
    {
        if (true === interface_exists(PasswordHasherFactoryInterface::class)) {
            $passwordHasherFactory = $this->container->get('security.password_hasher_factory');
            Assertion::isInstanceOf($passwordHasherFactory, PasswordHasherFactoryInterface::class);

            return $passwordHasherFactory->getPasswordHasher($user)->hash($user->getPlainPassword() ?? '');
        }

        $passwordEncoderFactory = $this->container->get('security.encoder_factory');
        Assertion::isInstanceOf($passwordEncoderFactory, EncoderFactoryInterface::class);

        return $passwordEncoderFactory->getEncoder($user)->encodePassword($user->getPlainPassword() ?? '', null);
    }

    private function verifyUserPassword(UserInterface $user, string $plain): bool
    {
        if (true === interface_exists(PasswordHasherFactoryInterface::class)) {
            $passwordHasherFactory = $this->container->get('security.password_hasher_factory');
            Assertion::isInstanceOf($passwordHasherFactory, PasswordHasherFactoryInterface::class);

            return $passwordHasherFactory->getPasswordHasher($user)->verify($user->getPassword() ?? '', $plain);
        }

        $passwordEncoderFactory = $this->container->get('security.encoder_factory');
        Assertion::isInstanceOf($passwordEncoderFactory, EncoderFactoryInterface::class);

        return $passwordEncoderFactory->getEncoder($user)->isPasswordValid($user->getPassword() ?? '', $plain, null);
    }
}
