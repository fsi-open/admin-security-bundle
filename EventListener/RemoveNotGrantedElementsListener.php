<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\AdminSecurityBundle\EventListener;

use FSi\Bundle\AdminBundle\Admin\ManagerInterface;
use FSi\Bundle\AdminSecurityBundle\Admin\SecuredElementInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RemoveNotGrantedElementsListener
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var ManagerInterface
     */
    private $adminManager;

    /**
     * @param ManagerInterface $adminManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    function __construct(ManagerInterface $adminManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->adminManager = $adminManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        foreach ($this->adminManager->getElements() as $element) {
            if ($element instanceof SecuredElementInterface) {
                if (!$element->isAllowed($this->authorizationChecker)) {
                    /* @var $element \FSi\Bundle\AdminBundle\Admin\ElementInterface */
                    $this->adminManager->removeElement($element->getId());
                }
            }
        }
    }
}