# UPGRADE from 1.0.* to ~1.1

This document describes steps needed to upgrade admin-security-bundle from 1.0.* version to ~1.1. Some of them always
require attention due to some BC breaks in 1.1 version, some of them often need some change and some are mentioned but
almost never should have been used in real use cases.

## Change your composer.json (always)

```yaml
# ...
    'fsi/admin-bundle': '~1.1'
# ...
```

**Version 1.1 drops support for symfony lower than 2.6 so if you are still using symfony < 2.6 you will have
to upgrade it too.**

## Remove FOSUserBundle (often)

The important difference between 1.0.* and ~1.1 is that the newer version does not require FOSUserBundle to persist
users through Doctrine although it can cooperate on the same user entity class if it implements ``UserInterface`` from
both bundles. So in most typical use case when your application have only administrative users you should
**remove this line** from ``app/AppKernel.php``:

```php
            new FOS\UserBundle\FOSUserBundle(),
```

## Configure the bundle (always)

Version 1.1 require some additional configuration. Please refer to the (installation section)[Resources/doc/installation.md]
for the details.

## Migrate user entities (always)

After successful bundle installation you should either upgrade your schema or create a migration. Either you want to
remove FOSUserBundle or leave it, the migration should be safe and not cause any loss of valuable data.

## Change method signature of SecuredElementInterface::isAllowed

New version requires this method to be defined as follows:

```php

namespace AppBundle\Admin;

use FSi\Bundle\AdminBundle\Annotation as Admin;
use FSi\Bundle\AdminBundle\Doctrine\Admin\CRUDElement;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Admin\Element()
 */
class SomeAdminElement extends CRUDElement implemends SecuredElementInterface
{
    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @return bool
     */
    public function isAllowed(AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    ...

}
```
