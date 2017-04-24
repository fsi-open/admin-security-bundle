Feature: Request URI for password change form
  In order to request link for password change
  As a admin panel user
  I need to fill in password reset request form

  Background:
    Given there is "admin@fsi.pl" user with role "ROLE_ADMIN" and password "admin"
    And I'm not logged in

  @email
  Scenario: Request reset link with invalid email address
    Given I am on the "Password Reset Request" page
    When I fill form with non-existent email address
    And I press "Send me instructions" button
    Then I should see message:
    """
    If Your account is active and the provided address is correct, You should receive instructions on resetting Your password.
    """
    And no emails were sent

  @email
  Scenario: Request reset link with valid email address
    Given I am on the "Password Reset Request" page
    When I fill form with correct email address
    And I press "Send me instructions" button
    Then I should see message:
    """
    If Your account is active and the provided address is correct, You should receive instructions on resetting Your password.
    """
    And I should receive email:
      | subject  | Reset Password           |
      | from     | from-admin@fsi.pl        |
      | to       | admin@fsi.pl             |
      | reply_to | do-not-reply@example.com |

  Scenario: Second request for reset password link
    Given user "admin@fsi.pl" has confirmation token "EwAq42G68-dg5Jl-HGr3Z7wII4cYh3sUvSpcdLhVxRQ"
    And I am on the "Password Reset Request" page
    When I fill form with correct email address
    And I press "Send me instructions" button
    Then I should see message:
    """
    If Your account is active and the provided address is correct, You should receive instructions on resetting Your password.
    """
    And I should be on the "Login" page
    And user "admin@fsi.pl" should still have confirmation token "EwAq42G68-dg5Jl-HGr3Z7wII4cYh3sUvSpcdLhVxRQ"
