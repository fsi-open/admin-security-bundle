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
    private ManagerInterface $manager;
    private TokenStorageInterface $tokenStorage;
    private AuthorizationCheckerInterface $authorizationChecker;

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
        if (false === $this->manager->hasElement($id)) {
            return false;
        }

        $element = $this->manager->getElement($id);
        if (true === $this->isAccessToElementRestricted($element)) {
            return false;
        }

        return true;
    }

    /**
     * @throws AccessDeniedException
     */
    public function getElement(string $id): Element
    {
        $element = $this->manager->getElement($id);
        if (true === $this->isAccessToElementRestricted($element)) {
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
        return array_filter(
            $this->manager->getElements(),
            fn(Element $element): bool
                => false === $this->isAccessToElementRestricted($element)
        );
    }

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

        if (true === $this->isUserForcedToChangePassword()) {
            return true;
        }

        return true === $element instanceof SecuredElementInterface
            && false === $element->isAllowed($this->authorizationChecker)
        ;
    }

    private function isUserForcedToChangePassword(): bool
    {
        if (false === $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return false;
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            throw new RuntimeException('User is authenticated fully, but there is no token in storage.');
        }

        $user = $token->getUser();
        if (false === $user instanceof EnforceablePasswordChangeInterface) {
            return false;
        }

        return $user->isForcedToChangePassword();
    }
}
