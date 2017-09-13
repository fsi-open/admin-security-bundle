# UPGRADE from 2.* to 3.*

## Do not use FSi\Bundle\AdminSecurityBundle\Security\FOS\FOSUser (rarely)

This class has been removed due to the bundle dropping integration with FOSUserBundle,
so if you want to use the two together, you will need to provide your own solution.

## Remove dependencies on deleted form services if using Symfony 3.*

The following services and parameters are not registered if you are using Symfony 3.*:

<table>    
    <thead>
        <tr>
            <th>Service</th>
            <th>Parameter</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>admin_security.form.change_password.type</td>
            <td>%admin_security.form.change_password.type.class%</td>
        </tr>
        <tr>
            <td>admin_security.form.type.password_reset.request</td>
            <td>%admin_security.form.type.password_reset.request.class%</td>
        </tr>
        <tr>
            <td>admin_security.form.type.password_reset.change_password</td>
            <td>%admin_security.form.type.password_reset.change_password.class%</td>
        </tr>
    </tbody>
</table>

so depending on any of these services or parameters is no longer valid. If you wish
to overwrite any classes defined in bundle's [configuration](configuration.md),
that is where you will need to change the values.

## Upgrade to PHP 7.1 or higher

In order to use this bundle, you will need PHP 7.1 or higher.
