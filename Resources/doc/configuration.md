# Configuration

```yml
fsi_admin_security:
    storage: null         # required
    enforce_secured_elements: false      # forces all registered admin elements to implement the SecuredElementInterface
    display_user_roles_form_field: true  # whether to display the choice field for user roles in `admin_security.form.type.user` form
    model:
        user: null        # required
    mailer:
        from: null
        reply_to: null
    activation:
        mailer:
          from: null      # required, copied from mailer.from if empty
          reply_to: null  # copied from mailer.reply_to if empty
          template: '@FSiAdminSecurity/Activation/mail/activation.html.twig'
          template_new_token: '@FSiAdminSecurity/Activation/mail/newToken.html.twig'
        token_ttl: 43200
        token_length: 32
        change_password_form:
            type: 'FSi\Bundle\AdminSecurityBundle\Form\Type\PasswordReset\ChangePasswordType'
            validation_groups: ['ResetPassword', 'Default']
    password_reset:
        mailer:
          from: null      # required, copied from mailer.from if empty
          reply_to: null  # copied from mailer.reply_to if empty
          template: '@FSiAdminSecurity/PasswordReset/mail/passwordReset.html.twig'
        token_ttl: 43200
        token_length: 32
        change_password_form:
            type: 'FSi\Bundle\AdminSecurityBundle\Form\Type\PasswordReset\ChangePasswordType'
            validation_groups: ['ResetPassword', 'Default']
    templates:
        activation:
            change_password: '@FSiAdminSecurity/Activation/changePassword.html.twig'
        password_reset:
            request: '@FSiAdminSecurity/PasswordReset/request.html.twig'
            change_password: '@FSiAdminSecurity/PasswordReset/changePassword.html.twig'
        login: '@FSiAdminSecurity/Security/login.html.twig'
        change_password: '@FSiAdminSecurity/Admin/changePassword.html.twig'
```
