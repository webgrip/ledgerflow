---
name: testing-strategy
description: Use when writing or reviewing tests, defining a test plan, choosing test levels, or improving CI quality.
---

# Testing Strategy Skill

## Use this when

- adding a feature
- fixing a bug
- changing domain or business logic
- changing auth/permissions
- changing AI behavior
- adding external integrations
- changing transaction, concurrency, or migration behavior

## Required test thinking

Cover:

- happy path
- validation failure
- authorization failure
- tenant isolation
- idempotency where relevant
- AI safety where relevant
- queued job behavior where relevant

## Test-level guidance

- use feature tests for request, policy, queue dispatch, and integration boundaries
- use focused unit tests for value objects, pure calculations, and domain services
- use contract-style tests when provider payloads or structured outputs matter
- add regression examples for risky AI or domain-critical behavior
- add negative tests for authorization and rejected state transitions

## Preferred style

Use behavior-focused Pest tests.

Examples:

```php
it('prevents users from accessing another tenant resource');
it('does not process the same external event twice');
it('treats ai output as advisory by default');
```
