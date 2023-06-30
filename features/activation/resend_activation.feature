Feature: Resend activation email
    As an inactive user
    If I did not receive my activation email
    Or my activation token has expired
    There should be an option to send a new email with a new activation token

  Background:
    Given there is "admin@fsi.pl" user with role "ROLE_ADMIN" and password "admin"
    And I'm logged in as admin
    Given I create disabled user with email address "user@example.com"
    And I clear the email pool

    @email
    Scenario: Resend active code
        And I am on the "User list" page
        When I resend activation token to the second user on the list
        Then I should be redirected to "User list" page
        And an email should be sent:
          | subject  | User activation - new token |
          | from     | activation@fsi.pl           |
          | to       | user@example.com            |
          | reply_to | do-not-reply@example.com    |


    Scenario: Activate user
      When I open activation page with token received by user "user@example.com" in the email
      Then I should be redirected to "Login" page
      And I should see message:
      """
      Your account has been successfully activated
      """
      And user "user@example.com" should be enabled
