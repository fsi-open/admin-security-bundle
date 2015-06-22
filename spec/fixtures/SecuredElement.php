<?php

namespace FSi\Bundle\AdminSecurityBundle\spec\fixtures;

use FSi\Bundle\AdminBundle\Admin\AbstractElement;
use FSi\Bundle\AdminSecurityBundle\Admin\SecuredElementInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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

    public function isAllowed(AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
    }
}
