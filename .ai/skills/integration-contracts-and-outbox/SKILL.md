---
name: integration-contracts-and-outbox
description: Use when adding or reviewing provider SDKs, webhooks, imports, exports, external APIs, anti-corruption layers, contract versioning, or outbox-style publishing.
---

# Integration Contracts and Outbox Skill

## Use this when

- adding a provider client
- consuming a webhook or import feed
- publishing external events or exports
- normalizing vendor payloads
- deciding whether an outbox or contract version is needed

## Working rules

- normalize provider payloads before they shape core domain code
- separate internal events from external contracts
- make duplicate delivery and replay behavior explicit
- version contracts deliberately when consumers depend on them
- prefer durable publish patterns for important side effects

## Review checklist

- is the provider boundary isolated?
- is payload normalization explicit?
- are retries, duplicates, and out-of-order delivery handled?
- is the external contract stable and documented?
- should this side effect be produced through an outbox-style flow?
