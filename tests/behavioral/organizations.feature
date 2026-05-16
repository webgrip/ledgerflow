Feature: Organization Management
  As a user
  I want to create and manage organizations
  So that I can separate financial data between entities

  Scenario: Create a new organization
    Given I am logged in
    When I navigate to "Create Organization"
    And I enter the organization name "Globex Corp"
    And I submit the form
    Then I should see "Globex Corp" as my active organization
    And I should be the owner of the organization

  Scenario: Switch between organizations
    Given I belong to multiple organizations
    When I click the organization switcher
    And I select a different organization
    Then my active organization should change
    And I should only see accounts for the new organization

  Scenario: Member cannot create accounts
    Given I am a member (not owner) of an organization
    When I try to create a new account
    Then I should see a permission denied error

  Scenario: Cross-organization data isolation
    Given user A owns "Org A" with account "A-Checking"
    And user B owns "Org B"
    When user B tries to view "A-Checking"
    Then user B should receive a 403 Forbidden response
