Feature: Secure admin top bar
  In order to allow admin users to change password or logout
  As a developer
  I need to install FSiAdminSecurityBundle in my application

  Background:
    Given There is "admin" user with role "ROLE_ADMIN" and password "admin"

  Scenario: Navigation top bar display
    Given I'm logged in as admin
    And I am on the "Admin panel" page
    Then I should see dropdown button in navigation bar "Hello admin"
    And "Hello admin" dropdown button should have following links
      | Link            |
      | Change password |
      | Logout          |

