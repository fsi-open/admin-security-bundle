<?php

namespace FSi\Bundle\AdminSecurityBundle\spec\fixtures;

use FSi\Bundle\AdminBundle\Admin\AbstractElement;
use FSi\Bundle\AdminSecurityBundle\Admin\SecuredElementInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SecuredElement extends AbstractElement implements SecuredElementInterface
{
    public function getId()
    {
    }

    public function getName()
    {
    }

    public function getRoute()
    {
    }

    public function isAllowed(SecurityContextInterface $securityContext)
    {
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }
}
