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
use FSi\FixturesBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class DataContext extends AbstractContext
{
    private UserPasswordEncoderInterface $passwordEncoder;
    private string $dbFilePath;

    public function __construct(
        Session $session,
        MinkParameters $minkParameters,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        string $projectDirectory
    ) {
        parent::__construct($session, $minkParameters, $entityManager);
        $this->passwordEncoder = $passwordEncoder;
        $this->dbFilePath = "{$projectDirectory}/var/data.sqlite";
    }

    /**
     * @BeforeScenario
     */
    public function createDatabase(): void
    {
        $this->deleteDatabaseIfExist();
        $manager = $this->getEntityManager();
        $metadata = $manager->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($manager);
        $tool->createSchema($metadata);
    }

    /**
     * @AfterScenario
     */
    public function deleteDatabaseIfExist(): void
    {
        if (true === file_exists($this->dbFilePath)) {
            unlink($this->dbFilePath);
        }
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
            $user->setPassword(
                $this->passwordEncoder->encodePassword($user, $password)
            );
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
     * @Then /^user password should be changed$/
     */
    public function userPasswordShouldBeChanged(): void
    {
        $user = $this->findUserByUsername('admin');

        Assertion::same(
            $user->getPassword(),
            $this->passwordEncoder->encodePassword($user, 'admin-new')
        );
    }

    /**
     * @Then /^user "([^"]*)" should have changed password$/
     */
    public function userShouldHaveChangedPassword(string $userEmail): void
    {
        $user = $this->findUserByEmail($userEmail);

        Assertion::same(
            $user->getPassword(),
            $this->passwordEncoder->encodePassword($user, 'admin-new')
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
}
