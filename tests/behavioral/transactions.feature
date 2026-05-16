Feature: Transaction Recording
  As an organization member
  I want to record financial transactions
  So that account balances stay accurate

  Background:
    Given I am logged in
    And I have an asset account "Checking" with currency "EUR"

  Scenario: Record a credit transaction
    When I record a credit of €100.00 with description "Client payment"
    Then the account balance should increase by €100.00
    And the transaction should appear in the activity list

  Scenario: Record a debit transaction
    Given the account has a balance of €500.00
    When I record a debit of €75.00 with description "Office supplies"
    Then the account balance should be €425.00

  Scenario: Transaction requires description
    When I try to submit a transaction without a description
    Then I should see a validation error

  Scenario: Filter transactions by type
    Given the account has both credit and debit transactions
    When I filter by type "Credit"
    Then I should only see credit transactions

  Scenario: Filter transactions by date range
    Given I have transactions from January, February, and March
    When I set the date range to February 1–28
    Then I should only see February transactions

  Scenario: Search transactions by description
    Given I have a transaction with description "Invoice from ACME"
    When I search for "ACME"
    Then I should see the matching transaction

  Scenario: AI explains a transaction
    Given I have a transaction "Payment to supplier"
    When I click "Explain"
    Then I should see an AI-generated explanation
    And the explanation should be advisory (not a financial recommendation)
