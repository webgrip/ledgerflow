Feature: Reconciliation
  As a finance team member
  I want to run reconciliation checks
  So that I can identify and resolve financial discrepancies

  Scenario: Run a reconciliation check
    Given I have transactions in the period January 1–31
    When I set the period to "January 2026"
    And I click "Run Reconciliation"
    Then a reconciliation run should be created
    And any discrepancies should appear as issues

  Scenario: View reconciliation issues
    Given a reconciliation run has completed with 2 open issues
    When I view the run detail page
    Then I should see 2 open issues
    And each issue should show the type and amount discrepancy

  Scenario: AI explains a reconciliation issue
    Given there is an open reconciliation issue
    When I click "Explain"
    Then I should see an AI-generated explanation of the mismatch
    And the explanation should suggest possible causes

  Scenario: Resolve a reconciliation issue
    Given there is an open issue "Missing transaction"
    When I click "Resolve"
    Then the issue status should change to "resolved"
    And the run's open issue count should decrease
