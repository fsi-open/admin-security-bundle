# Configuration

```yml
fsi_admin_security:
    model:
        user: null                                                              # required
    password_reset:
        mailer:
          from: null                                                            # required
          replay_to: office@example.com
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
