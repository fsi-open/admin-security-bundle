# UPGRADE from 4.* to 5.*

## Migrate users' roles stored in the application database

### Database column type change: `array` to `json`

In version 5.0, the `roles` column type has been changed from `array` to `json`, because the `array` type
has been removed in Doctrine DBAL 4.0. This means that if you are using the base User entity class from this
bundle and your users' roles are stored in the database, you will need to migrate the data to the new format.

To handle the whole process, you may perform the following steps:
1. Generate migration which changes the `roles` column type from `array` to `text` and execute it during deployment of
   the new code which uses `fsi/admin-security-bundle` >= 5.0.
2. Immediately run the following command to migrate the data from `array` format to `json` format:

```bash
php bin/console fsi:user:migrate-roles
```

3. Generate migration which changes the `roles` column type from `text` to `json` and execute it the next deployment
   of the new code.
