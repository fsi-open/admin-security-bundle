# UPGRADE from 1.0.* to ^2.0

This document describes steps needed to upgrade admin-security-bundle from 1.0.* version to ~2.0. Some of them always
require attention due to some BC breaks in 2.0 version, some of them often need some change and some are mentioned but
almost never should have been used in real use cases.

## Change your composer.json (always)

```yaml
# ...
    'fsi/admin-bundle': '^2.0'
# ...
```

**Version 2.0 drops support for symfony lower than 2.6 so if you are still using symfony < 2.6 you will have
to upgrade it too.**

**Also, a number of other dependencies had to be raised, so check `composer.json` to make sure
your application meets the minimum requirements**

## Remove FOSUserBundle (often)

The important difference between 1.0.* and ^2.0 is that the newer version does not require FOSUserBundle to persist
users through Doctrine although it can cooperate on the same user entity class if it implements ``UserInterface`` from
both bundles. So in most typical use case when your application have only administrative users you should
**remove this line** from ``app/AppKernel.php``:

```php
            new FOS\UserBundle\FOSUserBundle(),
```

## Configure the bundle (always)

Version 2.0 require some additional configuration. Please refer to the (installation section)[Resources/doc/installation.md]
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

## Change translation domain for custom login form errors (not often)

Since the login form now uses the `security` domain instead of `FSiAdminSecurity`,
you will have to move any custom login form error messages there.

## Implement UserRepositoryInterface for `admin_security.repository.user` service (not often)

During user related actions (activation, password reset etc.) the service `admin_security.repository.user`
is used to fetch users instances. If you have overwritten this service, you
will need to have it's class implement the `FSi\Bundle\AdminSecurityBundle\Security\User\UserRepositoryInterface`,
otherwise an exception will be thrown during compiler compilation.

## Replace logic based around RemoveNotGrantedElementsListener (very rarely)

As secured element access is now handled by the `admin_security.manager` service, the
following have been removed:

<table>
    <tbody>
        <tr>
            <td>Class</td>
            <td>FSi\Bundle\AdminSecurityBundle\EventListener\RemoveNotGrantedElementsListener</td>
        </tr>
        <tr>
            <td>Service</td>
            <td>admin_security.listener.remove_not_granted_elements</td>
        </tr>
        <tr>
            <td>Parameter</td>
            <td>admin_security.listener.remove_not_granted_elements.class</td>
        </tr>
    <tbody>
</table>

so if you have based any logic around these, you will need to adjust your application. 

# Adjust activation and change password routes (rarely)

`fsi_admin_activation` and `fsi_admin_security_password_reset_change_password`
routes have been slightly changed, so if you are overwriting them, you may need
to change your paths. Below is a table comparing changes:

<table>
    <thead>
        <tr>
            <th>Route</th>
            <th>Old path</th>
            <th>New path</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th>fsi_admin_activation</th>
            <th>/admin/activation/{activationToken}</th>
            <th>/admin/activation/activate/{activationToken}</th>
        </tr>
        <tr>
            <th>fsi_admin_security_password_reset_change_password</th>
            <th>/admin/password-reset/{confirmationToken}</th>
            <th>/admin/password-reset/change-password/{confirmationToken}</th>
        </tr>
    </tbody>
</table>
