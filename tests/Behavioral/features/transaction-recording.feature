Feature: Transaction Recording
  As an organization member
  I want to record financial transactions on accounts
  So that I can maintain an accurate financial ledger

  Background:
    Given I am logged in as "alice@demo.test"
    And I have an organization "Acme Corp"
    And I have an asset account "Main Checking" in USD

  Scenario: Recording a credit transaction
    When I navigate to the record transaction page for "Main Checking"
    And I select "Credit" as the transaction type
    And I enter "1000.00" as the amount
    And I enter "Client invoice payment" as the description
    And I select today's date
    And I click "Record"
    Then I should be redirected to the "Main Checking" account detail
    And I should see "Client invoice payment" in the transaction list
    And the account balance should show "1000.00"

  Scenario: Recording a debit transaction
    Given the account "Main Checking" has a balance of "$500.00"
    When I record a debit of "$200.00" with description "Office supplies"
    Then the account balance should show "300.00"
    And I should see "Office supplies" in the transaction list

  Scenario: Balance reflects credits minus debits
    When I record the following transactions:
      | Type   | Amount  | Description        |
      | credit | 5000.00 | Sales revenue      |
      | debit  | 1200.00 | Payroll            |
      | credit |  300.00 | Consulting fee     |
      | debit  |  450.00 | Software licenses  |
    Then the account balance should show "3650.00"

  Scenario: Amount is stored as minor units (no floating point error)
    When I record a credit of "$1234.56" with description "Precise amount"
    Then the stored amount_minor_units should be 123456
    And the displayed balance should show "1234.56"

  Scenario: Transaction amount must be positive
    When I try to record a transaction with amount "0.00"
    Then I should see a validation error for "amount"
    And no transaction should be recorded

  Scenario: Transaction description is required
    When I try to record a transaction without a description
    Then I should see a validation error for "description"

  Scenario: Non-member cannot record transactions on another org's account
    Given "carol@demo.test" is a member of "Globex LLC" only
    And "Acme Corp" has an account "Main Checking"
    When "carol@demo.test" tries to access the transaction create page for "Main Checking"
    Then she should receive a 403 Forbidden response
