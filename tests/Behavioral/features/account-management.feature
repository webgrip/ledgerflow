Feature: Account Management
  As an organization member
  I want to manage financial accounts
  So that I can track money flows within my organization

  Background:
    Given I am logged in as "alice@demo.test"
    And I have an organization "Acme Corp"

  Scenario: Creating a new asset account
    When I navigate to the create account page
    And I fill in "Account Name" with "Main Checking"
    And I select "Asset" as the account type
    And I set the currency to "USD"
    And I click "Create Account"
    Then I should be redirected to the account detail page
    And I should see "Main Checking"
    And the account balance should show "0.00"

  Scenario: Viewing the accounts list
    Given I have accounts:
      | Name              | Type    | Currency |
      | Main Checking     | Asset   | USD      |
      | Payroll Expense   | Expense | USD      |
    When I navigate to the accounts list
    Then I should see "Main Checking"
    And I should see "Payroll Expense"
    And I should not see accounts from other organizations

  Scenario: Account name is required
    When I navigate to the create account page
    And I leave "Account Name" blank
    And I click "Create Account"
    Then I should see a validation error for "name"
    And no account should be created

  Scenario: Account type must be valid
    When I navigate to the create account page
    And I fill in "Account Name" with "Test Account"
    And I do not select an account type
    And I click "Create Account"
    Then I should see a validation error for "type"

  Scenario: Non-member cannot view another organization's account
    Given "bob@demo.test" is a member of "Acme Corp" only
    And "carol@demo.test" has her own organization "Globex LLC"
    And "Globex LLC" has an account "Reserve Fund"
    When I am logged in as "bob@demo.test"
    And I try to view the "Reserve Fund" account directly
    Then I should receive a 403 Forbidden response

  Scenario: Creating accounts across all account types
    When I navigate to the create account page
    And I create an account for each type:
      | Name          | Type      |
      | Cash Account  | Asset     |
      | Loan Payable  | Liability |
      | Equity Fund   | Equity    |
      | Sales Income  | Revenue   |
      | Office Costs  | Expense   |
    Then I should have 5 accounts in my organization
