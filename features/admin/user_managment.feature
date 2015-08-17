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
    Then i should see following table:
      | Email            | Enabled? | Last login at | Role          |
      | admin            | Yes      |               | ROLE_ADMIN    |
      | red1@example.com | Yes      |               | ROLE_REDACTOR |
      | red2@example.com | Yes      |               | ROLE_REDACTOR |

  Scenario: Batch actions
    Given I am on the "User list" page
    Then i should have following list batch actions:
#      | Delete         |
#      | Password reset |
      | admin.user_list.batch_action.delete         |
      | admin.user_list.batch_action.password_reset |

  @javascript
  Scenario: Delete user
    Given I am on the "User list" page
    When i delete second user on the list
    Then i should see following table:
      | Email            | Enabled? | Last login at | Role          |
      | admin            | Yes      |               | ROLE_ADMIN    |
      | red2@example.com | Yes      |               | ROLE_REDACTOR |
