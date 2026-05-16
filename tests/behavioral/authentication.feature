Feature: User Authentication
  As a user
  I want to log in to LedgerFlow
  So that I can access my organization's financial data

  Background:
    Given the demo seed has been run
    And I am on the login page

  Scenario: Successful login
    When I enter email "alice@demo.test" and password "password"
    And I submit the login form
    Then I should be redirected to the dashboard
    And I should see "Acme Corp" in the organization switcher

  Scenario: Failed login with wrong password
    When I enter email "alice@demo.test" and password "wrongpassword"
    And I submit the login form
    Then I should see an authentication error
    And I should remain on the login page

  Scenario: Login required for protected pages
    Given I am not logged in
    When I visit the accounts page
    Then I should be redirected to the login page

  Scenario: Logout
    Given I am logged in as "alice@demo.test"
    When I click the logout button
    Then I should be redirected to the home page
    And I should not be able to access the dashboard
