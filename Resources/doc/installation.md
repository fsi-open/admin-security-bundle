# Installation in 5 simple steps

## 1. Download Admin Security Bundle

Add to composer.json

```
"require": {
    "fsi/admin-security-bundle": "dev-master"
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
```

## 4. Enable translations

```
# app/config/config.yml

framework:
    translator:      { fallback: %locale% }
```

## 5. Configure security.yml

```
# app/config/security.yml

security:
    encoders:
        Symfony\Component\Security\Core\User\User: plaintext

    providers:
        in_memory:
            memory:
                users:
                    admin: { password: admin, roles: [ 'ROLE_ADMIN' ] }

    firewalls:
        dev:
            pattern:  ^/(_(profiler|wdt)|css|images|js)/
            security: false

        admin_panel:
            pattern:    ^/admin
            form_login:
                check_path: fsi_admin_security_user_check
                login_path: fsi_admin_security_user_login
            logout:
                path:   fsi_admin_security_user_logout
            anonymous:    ~

    access_control:
        - { path: ^/admin/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin, roles: ROLE_ADMIN }
```

Security configuration when FOSUserBundle is used:

```
# app/config/security.yml

security:
    encoders:
        FOS\UserBundle\Model\UserInterface: sha512

    providers:
        fos_userbundle:
            id: fos_user.user_provider.username

    firewalls:
        dev:
            pattern:  ^/(_(profiler|wdt)|css|images|js)/
            security: false

        admin_panel:
            pattern:    ^/admin
            form_login:
                provider: fos_userbundle
                check_path: fsi_admin_security_user_check
                login_path: fsi_admin_security_user_login
            logout:
                path:   fsi_admin_security_user_logout
            anonymous:    ~

    access_control:
        - { path: ^/admin/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin, roles: ROLE_ADMIN }
```
