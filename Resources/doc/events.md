# Events

FSiAdminSecurityBundle provides some useful events which cause some default actions and allow to attach your own

## Change password event

``FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents::CHANGE_PASSWORD`` is fired when new password is set on
user instance. It has following built-in subscribers:

- [encode the new password](EventListener/UserEncodepasswordListener.php)
- [ensure user is no longer enforced to change password if it was before](EventListener/ClearChangePasswordEnforcementListener.php)

## Password reset request event

``FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents::RESET_PASSWORD_REQUEST`` is fired when user requests a
password reset. It has following built-in subscribers:

- [send an email to the user](EventListener/SendPasswordResetMailListener.php)

## User created event

``FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents::USER_CREATED`` is fired when a new user is created
from the console command. It has following built-in subscribers:

- [set user activation token if user is not enabled](EventListener/UserCreatedListener.php)
- [send an email to the user](EventListener/SendActivationMailListener.php)

## Activation event

``FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents::ACTIVATION`` is fired when user activates his/her account.
It has following built-in subscribers:

- [ensure user is enabled](EventListener/ActivateUserListener.php)

## Other listeners 

There is also a subscriber for all of the above events and it is responsible for saving changes to the
corresponding ORM/ODM - [PersistDoctrineUserListener](EventListener/PersistDoctrineUserListener.php) by the ODM/ORM.
