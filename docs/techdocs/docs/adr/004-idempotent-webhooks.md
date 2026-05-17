# ADR 004: Idempotent webhook ingestion with a provider_events table

## Status

Accepted

## Context

LedgerFlow receives webhook events from external payment providers. Providers may deliver the same event multiple times, and duplicate processing could double-count financial activity.

## Decision

Store incoming webhook events with a unique idempotency key and enforce idempotency in both the HTTP boundary and the queued job.

## Processing flow

```mermaid
flowchart TD
    Webhook[POST /webhooks/provider] --> Existing{Idempotency key exists?}
    Existing -->|Yes| Noop[Return successful no-op]
    Existing -->|No| Store[Store provider event as pending]
    Store --> Dispatch[Dispatch ProcessWebhookEvent]
    Dispatch --> Guard[Job guards against processed status]
    Guard --> Process[Process payload]
    Process --> Done[Mark processed and audit]
```

## Consequences

- Provider event storage grows over time and needs a pruning strategy.
- Each provider needs provider-specific signature validation.
