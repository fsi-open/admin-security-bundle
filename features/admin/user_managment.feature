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
      | Email            | Active | Password reset request | Activation request |
      | admin            | Yes    | No                     | No                 |
      | red1@example.com | Yes    | No                     | No                 |
      | red2@example.com | Yes    | No                     | No                 |

  Scenario: Batch actions
    Given I am on the "User list" page
    Then I should have following list batch actions:
      | Delete                  |
      | Reset password          |
      | Resend activation token |

  @javascript
  Scenario: Delete user
    Given I am on the "User list" page
    When I delete second user on the list
    Then I should see following table:
      | Email            | Active | Password reset request | Activation request |
      | admin            | Yes    | No                     | No                 |
      | red2@example.com | Yes    | No                     | No                 |

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
    And I should see following table:
      | Email            | Active | Password reset request | Activation request |
      | admin            | Yes    | No                     | No                 |
      | red1@example.com | Yes    | Yes                    | No                 |
      | red2@example.com | Yes    | No                     | No                 |

  @email
  Scenario: Create User
    Given I am on the "User list" page
    And I click "Add new" link
    When I fill form with valid user data
    And I press "Save" button
    And I am on the "User list" page
    Then I should see following table:
      | Email            | Active | Password reset request | Activation request |
      | admin            | Yes    | No                     | No                 |
      | red1@example.com | Yes    | No                     | No                 |
      | red2@example.com | Yes    | No                     | No                 |
      | new-user@fsi.pl  | No     | No                     | Yes                |
    And an email should be sent:
      | subject  | User Activation          |
      | from     | activation@fsi.pl        |
      | to       | new-user@fsi.pl          |
      | reply_to | do-not-reply@example.com |
