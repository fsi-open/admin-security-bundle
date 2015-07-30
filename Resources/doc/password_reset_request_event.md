# Password reset request event

FSiAdminSecurityBundle provides an event ``FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents::RESET_PASSWORD_REQUEST``
fired when user requested to reset his/her password. Built-in subscriber of this event
(send en email to the user)[EventListener/ResetPasswordRequestMailerListener.php] and ensure that the changes are
(flushed)[EventListener/DoctrineUserListener.php] by the OM/EM. So if you don't want doctrine to persist
your users you should write your own listener which will ensure that changes are persisted in the storage.
