Feature: Display secured admin elements that are allowed for logged user
  As a developer
  In order to limit the privileges of logged user
  I need to create secured admin elements

  Background:
    Given there is "admin@fsi.pl" user with role "ROLE_ADMIN" and password "admin"
    And there is "redactor@fsi.pl" user with role "ROLE_REDACTOR" and password "redactor"

  Scenario: Display navigation menu to admin
    Given I'm logged in as admin
    And I am on the "Admin panel" page
    Then I should see navigation menu with following elements
      | Element       |
      | Page settings |
      | News          |

  Scenario: Display navigation menu to redactor
    Given I'm logged in as redactor
    And I am on the "Admin panel" page
    Then I should see navigation menu with following elements
      | Element       |
      | News          |
    And I should not see "Page settings" position in menu