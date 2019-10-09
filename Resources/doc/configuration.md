# Configuration

```yml
fsi_admin_security:
    storage: null         # required
    model:
        user: null        # required
    mailer:
        from: null
        reply_to: null
    activation:
        mailer:
          from: null      # required, copied from mailer.from if empty
          reply_to: null  # copied from mailer.reply_to if empty
          template: '@FSiAdminSecurity/Activation/mail.html.twig'
          template_new_token: '@FSiAdminSecurity/Activation/mailNewToken.html.twig'
        token_ttl: 43200
        token_length: 32
        change_password_form:
            type: 'FSi\Bundle\AdminSecurityBundle\Form\Type\PasswordReset\ChangePasswordType' # Or 'admin_password_reset_change_password', if Symfony 2.*
            validation_groups: ['ResetPassword', 'Default']
    password_reset:
        mailer:
          from: null      # required, copied from mailer.from if empty
          reply_to: null  # copied from mailer.reply_to if empty
          template: '@FSiAdminSecurity/PasswordReset/mail.html.twig'
        token_ttl: 43200
        token_length: 32
        change_password_form:
            type: 'FSi\Bundle\AdminSecurityBundle\Form\Type\PasswordReset\ChangePasswordType' # Or 'admin_password_reset_change_password', if Symfony 2.*
            validation_groups: ['ResetPassword', 'Default']
    templates:
        activation:
            change_password: '@FSiAdminSecurity/Activation/change_password.html.twig'
        password_reset:
            request: '@FSiAdminSecurity/PasswordReset/request.html.twig'
            change_password: '@FSiAdminSecurity/PasswordReset/change_password.html.twig'
        login: '@FSiAdminSecurity/Security/login.html.twig'
        change_password: '@FSiAdminSecurity/Admin/change_password.html.twig'
```
