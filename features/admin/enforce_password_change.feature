Feature: Admin change password
  In order to allow admin users to change password
  As a developer
  I need to install FSiAdminSecurityBundle in my application

  Background:
    Given there is "redactor" user with role "ROLE_REDACTOR" and password "redactor" which is enforced to change password

  Scenario: Redirect to change password after successful login
    Given I am on the "Login" page
    When I fill form with login "redactor" and password "redactor"
    And I press "Login" button
    Then I should be redirected to "Admin change password" page
    And I should not see any menu elements

  Scenario: Can open admin panel after changing password
    Given I'm logged in as redactor
    And I change my password
    Then user "redactor" should have changed password
    And I should be redirected to "Login" page
    And I should see message:
    """
    Your password has been successfully changed
    """
