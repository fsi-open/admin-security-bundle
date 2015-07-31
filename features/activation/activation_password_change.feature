Feature: Activation of disabled user with enforced password change
  In order to activate my account
  As a newly created user
  I should receive an activation email and be enforced to change my password

  Background:
    Given I create disabled user with email address "user@example.com" and enforced password change

  @email
  Scenario: Send email to new user
    Then an email should be sent:
      | subject  | User Activation          |
      | from     | activation@fsi.pl        |
      | to       | user@example.com         |
      | reply_to | do-not-reply@example.com |

  Scenario: Open activation page with invalid activation token
    When i try open activation page with token "invalid-token"
    Then i should see 404 error

  Scenario: Open activation page with expired activation token
    Given user "user@example.com" has expired activation token "expired-token"
    When i try open activation page with token "expired-token"
    Then i should see 404 error

  Scenario: Submit activation form with valid data
    Given i open activation page with token received by user "user@example.com" in the email
    Then I should see message "Please setup a new password to activate your account"
    When i fill in new password with confirmation
    And I press "Activate account" button
    And I should be redirected to "Login" page
    Then user "user@example.com" should have changed password
    And I should see message "Your password has been successfully changed and your account has been activated"
    And user "user@example.com" should be enabled

  Scenario: Submit activation form with invalid data
    When i open activation page with token received by user "user@example.com" in the email
    And i fill in new password with invalid confirmation
    And I press "Activate account" button
    Then I should see information about passwords mismatch
