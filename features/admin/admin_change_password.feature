Feature: Admin change password
  In order to allow admin users to change password
  As a developer
  I need to install FSiAdminSecurityBundle in my application

  Background:
    Given there is "admin@fsi.pl" user with role "ROLE_ADMIN" and password "admin"
    And I'm logged in as admin

  Scenario: Access change password page
    Given I am on the "Admin panel" page
    Then I should see dropdown button in navigation bar "Hello admin"
    When I click "Change password" link from "Hello admin" dropdown button
    Then I should be redirected to "Admin change password" page

  Scenario: Change password page overview
    Given I am on the "Admin change password" page
    Then I should see page header with "Change password" content
    And I should see change password form with following fields
      | Field name       |
      | Current password |
      | New password     |
      | Repeat password  |
    And I should see change password form "Save" button

  Scenario: Submit change form with valid data
    And I am on the "Admin change password" page
    When I fill change password form fields with valid data
    And I press "Save"
    Then user "admin@fsi.pl" password should be changed
    And I should be redirected to "Login" page
    And I should see message:
    """
    Your password has been successfully changed
    """

  Scenario: Submit change form with invalid current password
    Given I am on the "Admin change password" page
    When I fill change password form fields with invalid current password
    And I press "Save"
    And I should see "Current password" field error in change password form with message
      """
      The entered password is invalid
      """

  Scenario: Submit change form with with different new repeated password
    Given I am on the "Admin change password" page
    When I fill change password form fields with repeat password different than new password
    And I press "Save"
    And I should see "New password" field error in change password form with message
      """
      The entered passwords don't match
      """
