# Events

FSiAdminSecurityBundle provides some useful events which cause some default actions and allow to attach your own

## Change password event

``FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents::CHANGE_PASSWORD`` is fired when new password is set on
user instance. Its build-in subscribers:

- (encode the new password)[EventListener/UserEncodepasswordListener.php]
- (ensure user is no longer enforced to change password if it was before)[EventListener/UserPasswordChangedListener.php]

## Password reset request event

``FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents::RESET_PASSWORD_REQUEST`` is fired when user requested to
reset his/her password. Its built-in subscribers:

- (send an email to the user)[EventListener/ResetPasswordRequestMailerListener.php]

## User created event

``FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents::USER_CREATED`` is fired when new user is created
from the console command. Its build-in subscribers:

- (set user activation token if user is not enabled)[EventListener/UserCreatedListener.php]
- (send an email to the user)[EventListener/ActivationMailerListener.php]

## Activation event

``FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents::ACTIVATION`` is fired when user activates his/her account.
Its build-in subscribers:

- (ensure user is enabled)[EventListener/ActivateUserListener.php]

## Other listeners 

There is also one subscriber which subscribes all of the above events and is responsible for saving changes to the
corresponding ORM/ODM - (DoctrineUserListener)[EventListener/DoctrineUserListener.php] by the ODM/ORM.
