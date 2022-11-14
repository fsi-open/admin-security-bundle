<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Session;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FriendsOfBehat\PageObjectExtension\Element\Element;
use FriendsOfBehat\SymfonyExtension\Mink\MinkParameters;
use FSi\Bundle\AdminSecurityBundle\Behat\Page\Page;

abstract class AbstractContext implements Context
{
    private Session $session;
    private MinkParameters $minkParameters;
    private EntityManagerInterface $entityManager;

    public function __construct(
        Session $session,
        MinkParameters $minkParameters,
        EntityManagerInterface $entityManager
    ) {
        $this->session = $session;
        $this->minkParameters = $minkParameters;
        $this->entityManager = $entityManager;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return EntityRepository<T>
     */
    protected function getRepository(string $className): EntityRepository
    {
        return $this->getEntityManager()->getRepository($className);
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    protected function getSession(): Session
    {
        return $this->session;
    }

    protected function getBaseUrl(): string
    {
        return $this->minkParameters['base_url'];
    }

    /**
     * @template T of Element
     * @param class-string<T> $elementClass
     * @return T
     */
    protected function getElement(string $elementClass): Element
    {
        return new $elementClass($this->session, $this->minkParameters);
    }

    /**
     * @template T of Page
     * @param class-string<T> $pageClass
     * @return T
     */
    protected function getPageObject(string $pageClass): Page
    {
        return new $pageClass($this->session, $this->minkParameters);
    }

    protected function isSeleniumDriverUsed(): bool
    {
        return $this->session->getDriver() instanceof Selenium2Driver;
    }
}
