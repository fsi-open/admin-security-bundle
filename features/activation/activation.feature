Feature: Activation of disabled user
  In order to activate my account
  As a newly created user
  I should receive an activation email

  Background:
    Given I create disabled user with email address "user@example.com"

  @email
  Scenario: Send email to new user
    Then an email should be sent:
      | subject  | User Activation          |
      | from     | activation@fsi.pl        |
      | to       | user@example.com         |
      | reply_to | do-not-reply@example.com |

  Scenario: Activate user
    When i open activation page with token received by user "user@example.com" in the email
    Then I should be redirected to "Login" page
    And I should see message "Your account has been successfully activated"
    And user "user@example.com" should be enabled
