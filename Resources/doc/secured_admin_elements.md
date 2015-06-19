# Secured Admin Elements

If you need to deny access to a specific admin element for some users
you should use ``SecuredElementInterface`` on admin element object.

Example:

```php
<?php

namespace FSi\FixturesBundle\Admin;

use FSi\Bundle\AdminBundle\Admin\Doctrine\CRUDElement;
use FSi\Bundle\AdminSecurityBundle\Admin\SecuredElementInterface;
use FSi\Component\DataGrid\DataGridFactoryInterface;
use FSi\Component\DataSource\DataSourceFactoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PageSettings extends CRUDElement implements SecuredElementInterface
{
    public function isAllowed(AuthorizationCheckerInterface $authorizationChecker)
    {
        return $authorizationChecker->isGranted('ROLE_ADMIN');
    }

    public function getClassName()
    {
    }

    public function getId()
    {
    }

    public function getName()
    {
    }

    protected function initDataGrid(DataGridFactoryInterface $factory)
    {
    }

    protected function initDataSource(DataSourceFactoryInterface $factory)
    {
    }

    protected function initForm(FormFactoryInterface $factory, $data = null)
    {
    }
}
```

As you can see there is extra method ``public function isAllowed(AuthorizationCheckerInterface $authorizationChecker)``
If this method return false admin element will be removed from admin elements manager, it will
be no longer visible in menu or even accessible through direct url.
