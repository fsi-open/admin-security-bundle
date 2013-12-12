Feature: Secure admin panel
  In order to prevent accessing admin panel by unauthorized users
  As a developer
  I need to install FSiAdminSecurityBundle in my application

  Scenario: Display login form to unauthorized users
    Given I'm not logged in
    When I open "Admin panel" page
    Then I should see login form with following fields:
      | Field name |
      | E-mail     |
      | Password   |
    And I should also see login form "Login" button

  Scenario: Login into admin panel as a admin
    Given I'm not logged in
    And I on the "Admin panel" page
    When I fill form with valid admin login and password
    And I press "Login" button
    Then I should be redirected to "Admin panel" page