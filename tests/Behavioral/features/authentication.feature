Feature: Authentication
  As a user of LedgerFlow
  I want to securely authenticate
  So that my financial data is protected

  Scenario: Successful login
    Given a user exists with email "user@example.com" and password "secret123"
    When I visit the login page
    And I enter "user@example.com" and "secret123"
    And I click "Log in"
    Then I should be redirected to the dashboard
    And I should be authenticated

  Scenario: Failed login with wrong password
    Given a user exists with email "user@example.com" and password "secret123"
    When I post to /login with email "user@example.com" and password "wrong"
    Then I should see an authentication error
    And I should not be authenticated

  Scenario: Failed login with non-existent email
    When I post to /login with email "nobody@nowhere.com" and password "anything"
    Then I should see an authentication error
    And I should not be authenticated

  Scenario: Successful registration
    When I register with name "New User", email "new@example.com", password "password123"
    Then a user account should be created for "new@example.com"
    And I should be authenticated

  Scenario: Registration fails with duplicate email
    Given a user exists with email "taken@example.com"
    When I try to register with email "taken@example.com"
    Then I should see a validation error
    And only one user should exist with that email

  Scenario: Protected routes redirect unauthenticated users
    Given I am not authenticated
    When I visit "/dashboard"
    Then I should be redirected to "/login"

  Scenario: Logout ends the session
    Given I am authenticated as "user@example.com"
    When I submit the logout form
    Then I should be unauthenticated
    And I should not be able to access "/dashboard" without logging in again
