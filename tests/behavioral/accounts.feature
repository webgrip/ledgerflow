Feature: Account Management
  As an organization owner
  I want to manage financial accounts
  So that I can track different streams of money

  Scenario: Create an asset account
    Given I am logged in as an organization owner
    When I navigate to "Create Account"
    And I fill in account name "Business Checking"
    And I select type "Asset" and currency "EUR"
    And I submit the form
    Then I should see "Business Checking" in my accounts list
    And the balance should be "€0.00"

  Scenario: Account types are enforced
    Given I am creating an account
    When I select "Revenue" as the account type
    Then revenue transactions should increase the balance
    And expense transactions should decrease the balance

  Scenario: Cannot create accounts in another org
    Given I am a member of "Org A"
    When I attempt to create an account via POST with "organization_id" set to "Org B"
    Then the account should be created in "Org A" only (organization scoped by auth)

  Scenario: Export account transactions as CSV
    Given I have an account with 3 transactions
    When I click "Export CSV"
    Then I should download a file named "<account>-transactions-<date>.csv"
    And the file should contain 4 rows (1 header + 3 data rows)
