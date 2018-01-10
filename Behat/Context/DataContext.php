<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Mink;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Tools\SchemaTool;
use FSi\FixturesBundle\Entity\User;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DataContext extends PageObjectContext implements KernelAwareContext, MinkAwareContext, SnippetAcceptingContext
{
    /**
     * @var Mink
     */
    private $mink;

    /**
     * @var array
     */
    private $minkParameters;

    /**
     * @var KernelInterface
     */
    private $kernel;

    public function setKernel(KernelInterface $kernel): void
    {
        $this->kernel = $kernel;
    }

    public function setMink(Mink $mink): void
    {
        $this->mink = $mink;
    }

    public function getMink(): Mink
    {
        return $this->mink;
    }

    public function setMinkParameters(array $parameters): void
    {
        $this->minkParameters = $parameters;
    }

    /**
     * @BeforeScenario
     */
    public function createDatabase()
    {
        $this->deleteDatabaseIfExist();
        $metadata = $this->getDoctrine()->getManager()->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($this->getDoctrine()->getManager());
        $tool->createSchema($metadata);
    }

    /**
     * @Given /^there is "([^"]*)" user with role "([^"]*)" and password "([^"]*)"$/
     */
    public function thereIsUserWithRoleAndPassword($email, $role, $password)
    {
        $user = new User();
        $user->setUsername($email);
        $user->setEmail($email);
        $user->addRole($role);
        $user->setPlainPassword($password);
        $user->setEnabled(true);

        if (0 !== strlen($password = $user->getPlainPassword())) {
            $encoder = $this->kernel->getContainer()->get('test.security.encoder_factory')->getEncoder($user);
            $user->setPassword($encoder->encodePassword($password, $user->getSalt()));
            $user->eraseCredentials();
        }

        $manager = $this->getDoctrine()->getManagerForClass(get_class($user));
        $manager->persist($user);
        $manager->flush();
    }

    /**
     * @Given there are following users:
     */
    public function thereAreFollowingUsers(TableNode $table)
    {
        $manager = $this->getDoctrine()->getManagerForClass('FSiFixturesBundle:User');

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
     * @Given /^there is "([^"]*)" user with role "([^"]*)" and password "([^"]*)" which is enforced to change password$/
     */
    public function thereIsUserWithRoleAndPasswordWhichIsEnforcedToChangePassword($nick, $role, $password)
    {
        $this->thereIsUserWithRoleAndPassword($nick, $role, $password);

        $manager = $this->kernel->getContainer()->get('doctrine')->getManagerForClass('FSiFixturesBundle:User');
        $userRepository = $manager->getRepository('FSiFixturesBundle:User');
        $user = $userRepository->findOneBy(['username' => $nick]);
        $user->enforcePasswordChange(true);
        $manager->flush();
    }

    /**
     * @Then /^user password should be changed$/
     */
    public function userPasswordShouldBeChanged()
    {
        $user = $this->findUserByUsername('admin');

        /** @var UserPasswordEncoderInterface $encoder */
        $encoder = $this->kernel->getContainer()->get('security.password_encoder');

        expect($user->getPassword())->toBe($encoder->encodePassword($user, 'admin-new'));
    }

    /**
     * @Then /^user "([^"]*)" should have changed password$/
     */
    public function userShouldHaveChangedPassword($userEmail)
    {
        $user = $this->findUserByEmail($userEmail);

        $encoder = $this->kernel->getContainer()->get('security.password_encoder');

        expect($user->getPassword())->toBe($encoder->encodePassword($user, 'admin-new'));
    }

    /**
     * @Then /^user "([^"]*)" should be enabled$/
     */
    public function userShouldBeEnabled($userEmail)
    {
        $user = $this->findUserByEmail($userEmail);

        expect($user->isEnabled())->toBe(true);
    }

    /**
     * @AfterScenario
     */
    public function deleteDatabaseIfExist()
    {
        $dbFilePath = $this->kernel->getRootDir() . '/data.sqlite';

        if (file_exists($dbFilePath)) {
            unlink($dbFilePath);
        }
    }

    /**
     * @return Registry
     */
    protected function getDoctrine()
    {
        return $this->kernel->getContainer()->get('doctrine');
    }

    private function findUserByUsername(string $username): User
    {
        return $this->getDoctrine()->getRepository('FSiFixturesBundle:User')->findOneBy(['username' => $username]);
    }

    private function findUserByEmail(string $userEmail): User
    {
        return $this->getDoctrine()->getRepository('FSiFixturesBundle:User')->findOneBy(['email' => $userEmail]);
    }
}
