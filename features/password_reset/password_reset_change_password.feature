Feature:
  In order to change last password
  As a admin panel user
  I need visit special link

  Background:
    Given there is "admin@fsi.pl" user with role "ROLE_ADMIN" and password "admin"
    And user "admin@fsi.pl" has confirmation token "EwAq42G68-dg5Jl-HGr3Z7wII4cYh3sUvSpcdLhVxRQ"
    And I'm not logged in

  Scenario: Open password change page with invalid token
    When I try open password change page with token "invalid-token"
    Then I should see 404 error

  Scenario: Open password change page with expired token
    Given user "admin@fsi.pl" has expired confirmation token "expired-token"
    When I try open password change page with token "expired-token"
    Then I should see 404 error

  Scenario: Submit change password form with valid data
    When I open password change page with token "EwAq42G68-dg5Jl-HGr3Z7wII4cYh3sUvSpcdLhVxRQ"
    And I fill in new password with confirmation
    And I press "Change password" button
    And I should be redirected to "Login" page
    Then user "admin@fsi.pl" should have changed password
    And I should see message:
    """
    Your password has been successfully changed
    """

  Scenario: Submit change password form with invalid data
    When I open password change page with token "EwAq42G68-dg5Jl-HGr3Z7wII4cYh3sUvSpcdLhVxRQ"
    And I fill in new password with invalid confirmation
    And I press "Change password" button
    Then I should see information about passwords mismatch
