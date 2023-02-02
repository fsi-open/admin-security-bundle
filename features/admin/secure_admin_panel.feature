Feature: Secure admin panel
  In order to prevent accessing admin panel by unauthorized users
  As a developer
  I need to install FSiAdminSecurityBundle in my application

  Background:
    Given there is "admin@fsi.pl" user with role "ROLE_ADMIN" and password "admin"
    And I'm not logged in

  Scenario: Display login form to unauthorized users
    When I try to open "Admin panel" page
    Then I should be redirected to "Login" page
    And I should see login form with following fields:
      | Field name |
      | E-mail     |
      | Password   |
    And I should also see login form "Login" button

  Scenario: Login into admin panel as a admin
    Given I am on the "Login" page
    When I fill form with valid admin login and password
    And I press "Login" button
    Then I should be redirected to "Admin panel" page

  Scenario: Login into admin panel using bad credentials
    Given I am on the "Login" page
    When I fill form with invalid admin login and password
    And I press "Login" button
    And I should see message:
    """
    Invalid credentials.
    """

  Scenario: Logout from admin panel
    Given I'm logged in as admin
    And I am on the "Admin panel" page
    When I click "Logout" link from "Hello admin" dropdown button
    Then I should be logged off
