Feature: Webhook Ingestion
  As a payment provider integration
  I want to send webhook events to LedgerFlow
  So that payments are recorded automatically

  Scenario: Accept a new webhook event
    When I POST to "/webhooks/stripe" with a payment_intent.succeeded payload
    Then the response should be 202 Accepted
    And the event should be stored with status "pending"
    And a processing job should be queued

  Scenario: Reject a duplicate webhook event
    Given the event "evt_001" has already been received
    When I POST "/webhooks/stripe" again with event id "evt_001"
    Then the response should be 200 OK (silently ignored)
    And no duplicate event should be created

  Scenario: Reject a webhook with invalid signature
    Given the STRIPE_WEBHOOK_SECRET is configured
    When I POST to "/webhooks/stripe" with an invalid signature header
    Then the response should be 401 Unauthorized
    And no event should be stored

  Scenario: Accept webhook without signature in demo mode
    Given no STRIPE_WEBHOOK_SECRET is configured
    When I POST to "/webhooks/stripe" without a signature header
    Then the response should be 202 Accepted (signature validation skipped)

  Scenario: Replay a failed webhook
    Given a webhook event has status "failed"
    And I am an organization owner
    When I click "Replay" on the webhook event
    Then the event status should reset to "pending"
    And a new processing job should be dispatched
