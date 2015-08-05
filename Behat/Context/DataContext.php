<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Mink;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\ORM\Tools\SchemaTool;
use FSi\FixturesBundle\Entity\User;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Symfony\Component\HttpKernel\KernelInterface;

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

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function setMink(Mink $mink)
    {
        $this->mink = $mink;
    }

    /**
     * @return Mink
     */
    public function getMink()
    {
        return $this->mink;
    }

    /**
     * {@inheritdoc}
     */
    public function setMinkParameters(array $parameters)
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
    public function thereIsUserWithRoleAndPassword($nick, $role, $password)
    {
        $user = new User();

        $user->setUsername($nick)
            ->setEmail($nick)
            ->setRoles(array($role))
            ->setPlainPassword($password)
            ->setEnabled(true);

        if (0 !== strlen($password = $user->getPlainPassword())) {
            $encoder = $this->kernel->getContainer()->get('security.encoder_factory')->getEncoder($user);
            $user->setPassword($encoder->encodePassword($password, $user->getSalt()));
            $user->eraseCredentials();
        }

        $manager = $this->kernel->getContainer()->get('doctrine')->getManagerForClass('FSiFixturesBundle:User');
        $manager->persist($user);
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
        $user = $userRepository->findOneBy(array('username' => $nick));
        $user->enforcePasswordChange(true);
        $manager->flush();
    }

    /**
     * @Then /^user password should be changed$/
     */
    public function userPasswordShouldBeChanged()
    {
        $user = $this->findUserByUsername('admin');

        /** @var \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $encoder */
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
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected function getDoctrine()
    {
        return $this->kernel->getContainer()->get('doctrine');
    }

    /**
     * @param $username
     * @return User
     */
    private function findUserByUsername($username)
    {
        return $this->getDoctrine()->getRepository('FSiFixturesBundle:User')->findOneBy(array('username' => $username));
    }

    /**
     * @param $userEmail
     * @return User
     */
    private function findUserByEmail($userEmail)
    {
        return $this->getDoctrine()->getRepository('FSiFixturesBundle:User')->findOneBy(array('email' => $userEmail));
    }
}
