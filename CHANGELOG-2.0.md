# CHANGELOG FOR VERSION 2.0

# Removed dependency from FOSUserBundle

No longer are you forced to use it, but just in case there is a `FSi\Bundle\AdminSecurityBundle\Security\FOS\FOSUser`
base class, which you can extend if you still would like to retain the mix.

# Added a default administration element for users

We have introduced an element (`FSi\Bundle\AdminSecurityBundle\Doctrine\Admin\UserElement`)
which gives you a basic management over users defined by class in the `fsi_admin_security.model.user`
configuration parameter. This element also has a batch action for resetting users'
passwords.

# Introduced console commands for user actions

Checkout the `Command` folder for available console user actions, currently there
are:

- Create user (`fsi:user:create`),
- activate / deactivate user (`fsi:user:activate` / `fsi:user:deactivate`),
- promote / demote user (`fsi:user:promote` / `fsi:user:demote`),
- change password (`fsi:user:change-password`)

actions available. Be sure to checkout their documentation before using them.

# New user activation via email

By default, new users created by the default administration element (`admin_security_user`)
will not be enabled and an activation email with a a link to enable their account
will be sent to their email box. You can specify from and reply to values for
the sent message, just check the [configuration](Resources/doc/configuration.md)
(remember that you can set a seperate set of values for activation, password
reset and general use).

# Reset password by email

In a similar fashion to user activation, an option to reset password via email
has been added. Just add a link pointing to the `fsi_admin_security_password_reset_request`
path somewhere in a place reachable by anonymous users and you are good to go.
Again, please refer to the [configuration](Resources/doc/configuration.md) for
more information on customizing this action.

# Login form errors displayed by flash messages

Previously these were displayed as form errors, that used the `FSiAdminSecurity`
translation domain. Now they are displayed through flash messages using the default
`security` domain.
