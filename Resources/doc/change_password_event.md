# Change password event

FSiAdminSecurityBundle provides an event ``FSi\Bundle\AdminSecurityBundle\Event\AdminSecurityEvents::CHANGE_PASSWORD``
fired when new password is set on user instance. Built-in subscribers of this event
(encode the new password)[EventListener/UserEncodepasswordListener.php] and ensure that the changes are
(flushed)[EventListener/DoctrineUserListener.php] by the OM/EM. So if you don't want doctrine to persist
your users you should write your own listener which will ensure that changes are persisted in the storage.
