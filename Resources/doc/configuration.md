# Configuration

```yml
fsi_admin_security:
    driver: null                                                                # required
    model:
        user: null                                                              # required
    mailer:
        from: null
        reply_to: null
    activation:
        mailer:
          from: null                                                            # required, copied from mailer.from if empty
          reply_to: null                                                        # copied from mailer.reply_to if empty
          template: '@FSiAdminSecurity/Activation/mail.html.twig'
        token_ttl: 43200
        token_length: 32
    password_reset:
        mailer:
          from: null                                                            # required, copied from mailer.from if empty
          reply_to: null                                                        # copied from mailer.reply_to if empty
          template: '@FSiAdminSecurity/PasswordReset/mail.html.twig'
        token_ttl: 43200
        token_length: 32
    templates:
        password_reset:
            request: '::request.html.twig'
            change_password: '::change_password.html.twig'
        login: 'FSiAdminSecurityBundle:Security:login.html.twig'
        change_password: 'FSiAdminSecurityBundle:Admin:change_password.html.twig'
```
