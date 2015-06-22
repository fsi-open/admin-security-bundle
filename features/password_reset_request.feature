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
    And I press "Send me reset password instructions" button
    Then I should see password reset request form message "Reset password instructions sent"
    And no emails were sent

  @email
  Scenario: Request reset link with valid email address
    Given I am on the "Password Reset Request" page
    When I fill form with correct email address
    And I press "Send me reset password instructions" button
    Then I should see password reset request form message "Reset password instructions sent"
    And I should receive email:
      | subject   | Reset Password     |
      | from      | from-admin@fsi.pl  |
      | to        | admin@fsi.pl       |
      | replay_to | office@example.com |

  Scenario: Second request for reset password link
    Given user "admin@fsi.pl" has confirmation token "EwAq42G68-dg5Jl-HGr3Z7wII4cYh3sUvSpcdLhVxRQ"
    And I am on the "Password Reset Request" page
    When I fill form with correct email address
    And I press "Send me reset password instructions" button
    Then I should see message "You already requested password reset instructions. Check your email."
    And I should be on the "Password Reset Request" page
    And user "admin@fsi.pl" should still have confirmation token "EwAq42G68-dg5Jl-HGr3Z7wII4cYh3sUvSpcdLhVxRQ"
