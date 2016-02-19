# Installation

## 1. Download Admin Security Bundle

Add to composer.json

```
"require": {
    "fsi/admin-security-bundle": "~2.0@dev"
}
```

## 2. Register bundles

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // Bundles required by FSiAdminBundle
        new Knp\Bundle\MenuBundle\KnpMenuBundle(),
        new FSi\Bundle\DataSourceBundle\DataSourceBundle(),
        new FSi\Bundle\DataGridBundle\DataGridBundle(),
        new FSi\Bundle\AdminBundle\FSiAdminBundle(),

        // FSiAdminSecureBundle
        new FSi\Bundle\AdminSecurityBundle\FSiAdminSecurityBundle()
    );
}
```

## 3. Configure routing

```
# app/config/routing.yml

admin:
    resource: "@FSiAdminBundle/Resources/config/routing/admin.yml"
    prefix: /admin

admin_security:
    resource: "@FSiAdminSecurityBundle/Resources/config/routing/admin_security.yml"
    prefix: /admin

admin_activation:
    resource: "@FSiAdminSecurityBundle/Resources/config/routing/admin_activation.yml"
    prefix: /admin

admin_password_reset:
    resource: "@FSiAdminSecurityBundle/Resources/config/routing/admin_password_reset.yml"
    prefix: /admin
```

The last two routing entries are optional if you don't want to use these built-in features.

## 4. Create your user class

The most common case is to extend the base entity user class provided by this bundle. If you don't want or don't need
to use FOSUserBundle you should choose bare user entity:

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FSi\Bundle\AdminSecurityBundle\Entity\User as BaseUser;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
}
```

If you also need FOSUserBundle features on the same entity (i.e because you already use them in your app) you should
choose special compatibility entity class:

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FSi\Bundle\AdminSecurityBundle\Entity\FOSUser;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user")
 */
class User extends FOSUser
{
}
```

## 5. Configure fsi_admin_security

Minimal required configuration:

```yml
# app/config/config.yml

fsi_admin_security:
    storage: orm
    firewall_name: admin_panel
    mailer:
        from: admin@example.com
    model:
        user: AppBundle\User
```

## 6. Configure security.yml

```
# app/config/security.yml

security:
    encoders:
        FSi\Bundle\AdminSecurityBundle\Security\User\UserInterface: sha512

    providers:
        entity_provider:
            entity:
                class: AppBundle:User
                property: email

    firewalls:
        dev:
            pattern:  ^/(_(profiler|wdt)|css|images|js)/
            security: false

        admin_panel:
            pattern:    ^/admin
            form_login:
                provider: entity_provider
                check_path: fsi_admin_security_user_check
                login_path: fsi_admin_security_user_login
            logout:
                path:   fsi_admin_security_user_logout
            anonymous:    ~

    access_control:
        - { path: ^/admin/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin/password-reset/, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin/activation/, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin, roles: ROLE_ADMIN }
```

### Bcrypt encoder

If you want to use bcrypt encoder, you must clear the salt that is set in user constructor. Symfony >= 2.8 ignores salt.
```
class User extends FSi\Bundle\AdminSecurityBundle\Security\User\User
{
    public function __construct()
    {
        parent::__construct();
        $this->salt = null;
    }

    // ...
}
```

Now when your admin panel is secured you should read how to [secure specific admin elements](secured_admin_elements.md).
