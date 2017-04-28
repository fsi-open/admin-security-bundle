<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\Admin;

use FSi\Bundle\AdminBundle\Admin\Element;
use FSi\Bundle\AdminBundle\Admin\ManagerInterface;
use FSi\Bundle\AdminBundle\Admin\Manager\Visitor;
use FSi\Bundle\AdminSecurityBundle\Admin\SecuredElementInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SecuredManager implements ManagerInterface
{
    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(
        ManagerInterface $manager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function addElement(Element $element)
    {
        return $this->manager->addElement($element);
    }

    /**
     * {@inheritdoc}
     */
    public function hasElement($id)
    {
        if (!$this->manager->hasElement($id)) {
            return false;
        }

        $element = $this->manager->getElement($id);
        if ($this->isAccessToElementRestricted($element)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $id
     * @return \FSi\Bundle\AdminBundle\Admin\Element|null
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function getElement($id)
    {
        $element = $this->manager->getElement($id);
        if (!$element) {
            return null;
        }

        if ($this->isAccessToElementRestricted($element)) {
            throw new AccessDeniedException(sprintf(
                'Access denied to element "%s"',
                get_class($element)
            ));
        }

        return $element;
    }

    /**
     * {@inheritdoc}
     */
    public function removeElement($id)
    {
        $this->manager->removeElement($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getElements()
    {
        return array_filter((array) $this->manager->getElements(), function (Element $element) {
            return !$this->isAccessToElementRestricted($element);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Visitor $visitor)
    {
        $visitor->visitManager($this);
    }

    /**
     * @param Element $element
     * @return boolean
     */
    private function isAccessToElementRestricted(Element $element)
    {
        if (!$this->tokenStorage->getToken()) {
            // The request is not behind a firewall, so all elements are restricted
            return true;
        }

        return $element instanceof SecuredElementInterface
            && !$element->isAllowed($this->authorizationChecker)
        ;
    }
}
