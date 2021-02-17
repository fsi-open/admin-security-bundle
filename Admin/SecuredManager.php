<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\AdminSecurityBundle\Admin;

use FSi\Bundle\AdminBundle\Admin\Element;
use FSi\Bundle\AdminBundle\Admin\Manager\Visitor;
use FSi\Bundle\AdminBundle\Admin\ManagerInterface;
use FSi\Bundle\AdminSecurityBundle\Security\User\EnforceablePasswordChangeInterface;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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

    public function addElement(Element $element): void
    {
        $this->manager->addElement($element);
    }

    public function hasElement(string $id): bool
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
     * @return Element
     * @throws AccessDeniedException
     */
    public function getElement(string $id): Element
    {
        $element = $this->manager->getElement($id);

        if ($this->isAccessToElementRestricted($element)) {
            throw new AccessDeniedException(sprintf(
                'Access denied to element "%s"',
                get_class($element)
            ));
        }

        return $element;
    }

    public function removeElement(string $id): void
    {
        $this->manager->removeElement($id);
    }

    public function getElements(): array
    {
        return array_filter($this->manager->getElements(), function (Element $element): bool {
            return false === $this->isAccessToElementRestricted($element);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Visitor $visitor): void
    {
        $visitor->visitManager($this);
    }

    private function isAccessToElementRestricted(Element $element): bool
    {
        if (null === $this->tokenStorage->getToken()) {
            // The request is not behind a firewall, so all elements are restricted
            return true;
        }

        if ($this->isUserForcedToChangePassword()) {
            return true;
        }

        return true === $element instanceof SecuredElementInterface
            && false === $element->isAllowed($this->authorizationChecker);
    }

    private function isUserForcedToChangePassword(): bool
    {
        if (false === $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return false;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        if (false === $user instanceof EnforceablePasswordChangeInterface) {
            return false;
        }

        return $user->isForcedToChangePassword();
    }
}
