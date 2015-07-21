# Change password event

Because FSiAdminSecurityBundle doesn't care about your user model as long as it implements
``Symfony\Component\Security\Core\User\UserInterface`` there is no simple way to save
changed password.
That is why ChangePasswordEvent exist. Each time user change his password the
``admin.security.change_password`` is dispatched.
By default FSiAdminSecureBundle provide ``DoctrineUserListener`` event listener.
It will check if user from security context token is Doctrine entity next it will try to
set encoded password on it (using Symfony2 PropertyAccessor) and save change to database.

If your user model is not a Doctrine entity nothing will happen that's why if you dont
use Doctrine you will need to create your own change password event listener.
