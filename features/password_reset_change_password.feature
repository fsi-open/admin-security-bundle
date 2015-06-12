Feature:
  In order to change last password
  As a admin panel user
  I need visit special link

  Background:
    Given there is "admin@fsi.pl" user with role "ROLE_ADMIN" and password "admin"
    And user "admin@fsi.pl" has confirmation token "EwAq42G68-dg5Jl-HGr3Z7wII4cYh3sUvSpcdLhVxRQ"
    And I'm not logged in

  Scenario: Open password change page with invalid token
    When i try open password change page with token "invalid-token"
    Then i should see 404 error

  Scenario: Open password change page with expired token
    Given user "admin@fsi.pl" has expired confirmation token "expired-token"
    When i try open password change page with token "expired-token"
    Then i should see 404 error

  Scenario: Submit change password form with valid data
    When i open password change page with token "EwAq42G68-dg5Jl-HGr3Z7wII4cYh3sUvSpcdLhVxRQ"
    And i fill in new password with confirmation
    And I press "Change password" button
    And I should be redirected to "Login" page
    And I should see message "Your password has been changed successfully"

  Scenario: Submit change password form with invalid data
    When i open password change page with token "EwAq42G68-dg5Jl-HGr3Z7wII4cYh3sUvSpcdLhVxRQ"
    And i fill in new password with invalid confirmation
    And I press "Change password" button
    And I should see information about passwords mismatch
