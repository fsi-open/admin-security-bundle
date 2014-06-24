<?php

namespace FSi\Bundle\AdminSecurityBundle\spec\fixtures;

use FSi\Bundle\AdminBundle\Admin\AbstractElement;
use FSi\Bundle\AdminSecurityBundle\Admin\SecuredElementInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SecuredElement extends AbstractElement implements SecuredElementInterface
{

    /**
     * ID will appear in routes:
     * - http://example.com/admin/{name}/list
     * - http://example.com/admin/{name}/edit
     * etc.
     *
     * @return string
     */
    public function getId()
    {
    }

    /**
     * Name is a simple string that can be translated.
     *
     * @return string
     */
    public function getName()
    {
    }

    /**
     * Return route name that will be used to generate element url in menu.
     *
     * @return string
     */
    public function getRoute()
    {
    }

    /**
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @return Boolean
     */
    public function isAllowed(SecurityContextInterface $securityContext)
    {
    }

    public function setDefaultOptions(OptionsResolverInterface $optionsResolver)
    {
    }
}
