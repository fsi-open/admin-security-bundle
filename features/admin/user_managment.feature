Feature: User management

  Background:
    Given there is "admin" user with role "ROLE_ADMIN" and password "admin"
    And I'm logged in as admin
    And there are following users:
      | Email            | Role          |
      | red1@example.com | ROLE_REDACTOR |
      | red2@example.com | ROLE_REDACTOR |

  Scenario: Display list of users
    Given I am on the "User list" page
    Then I should see following table:
      | Email            | Enabled? | Last login at | Role          |
      | admin            | Yes      |               | ROLE_ADMIN    |
      | red1@example.com | Yes      |               | ROLE_REDACTOR |
      | red2@example.com | Yes      |               | ROLE_REDACTOR |

  Scenario: Batch actions
    Given I am on the "User list" page
    Then I should have following list batch actions:
#      | Delete         |
#      | Password reset |
      | admin.user_list.batch_action.delete         |
      | admin.user_list.batch_action.password_reset |

  @javascript
  Scenario: Delete user
    Given I am on the "User list" page
    When I delete second user on the list
    Then I should see following table:
      | Email            | Enabled? | Last login at | Role          |
      | admin            | Yes      |               | ROLE_ADMIN    |
      | red2@example.com | Yes      |               | ROLE_REDACTOR |

  @email
  @javascript
  Scenario: Reset password
    Given I am on the "User list" page
    When I reset password for the second user on the list
    Then an email should be sent:
      | subject  | Reset Password           |
      | from     | from-admin@fsi.pl        |
      | to       | red1@example.com         |
      | reply_to | do-not-reply@example.com |

