# ADR 004: Idempotent webhook ingestion with a provider_events table

## Status
Accepted

## Context
LedgerFlow receives webhook events from external payment providers (Stripe, Mollie). Payment providers may deliver the same event multiple times. Processing a duplicate payment event could cause double-counting of revenue.

## Decision
Store all incoming webhook events in a `provider_events` table with a unique `idempotency_key`. Implement two-layer idempotency: in the HTTP controller (before job dispatch) and in the job itself (before processing).

## Idempotency key resolution
1. Stripe-style `id` field in payload → `{provider}:{id}`
2. `Stripe-Event-Id` header → `{provider}:{header_value}`
3. Fallback: SHA-256 hash of `{provider}:{raw_body}`

## Processing flow
```
POST /webhooks/{provider}
  → Check idempotency_key exists → 200 (no-op)
  → Create WebhookEvent (status=Pending)
  → Dispatch ProcessWebhookEvent job → 202
    → Job checks status !== Processed (guard)
    → markProcessing()
    → process payload
    → markProcessed()
```

## Alternatives considered
### Stateless idempotency via Redis locks
Simpler but loses the audit trail of all received events and their processing status.

### Process synchronously in the controller
Tight coupling, no retry logic, slower response to provider.

## Consequences
- The `provider_events` table grows indefinitely — a pruning strategy should be added in a future milestone
- Webhook signature validation is stubbed; each provider needs its own implementation (Stripe HMAC, Mollie token)
