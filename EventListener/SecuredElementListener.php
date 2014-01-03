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
use Symfony\Component\Security\Core\SecurityContextInterface;

class SecuredElementListener
{
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var ManagerInterface
     */
    private $adminManager;

    /**
     * @param ManagerInterface $adminManager
     * @param SecurityContextInterface $securityContext
     */
    function __construct(ManagerInterface $adminManager, SecurityContextInterface $securityContext)
    {
        $this->adminManager = $adminManager;
        $this->securityContext = $securityContext;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $token = $this->securityContext->getToken();

        if (!isset($token)) {
            return;
        }

        foreach ($this->adminManager->getElements() as $element) {
            if ($element instanceof SecuredElementInterface) {
                if (!$element->isAllowed($this->securityContext)) {
                    /* @var $element \FSi\Bundle\AdminBundle\Admin\ElementInterface */
                    $this->adminManager->removeElement($element->getId());
                }
            }
        }
    }
}