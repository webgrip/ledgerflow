# Engineering Principles

## Modular monolith

Use a modular monolith.

Do not create microservices unless there is a specific reason.

Use domain-driven design where the problem space is complex.

Prefer clear bounded contexts, explicit domain language, and module boundaries that match the business domain.

Suggested high-level modules:

- Identity
- Organizations
- Accounts
- Ledger
- Payments
- Reconciliation
- Audit
- Reporting
- AI
- Operations

## Ubiquitous language and boundaries

Prefer names that come from the domain, not from generic technical patterns.

Keep terms stable across code, tests, docs, and UI.

When a term is overloaded, define one meaning and use it consistently.

## Invariants and ownership

Critical business rules should have a clear owner.

Do not spread the same invariant across controllers, jobs, policies, validators, and UI code without a single authoritative place.

For important workflows, be able to answer:

- what rule must always hold?
- where is that rule enforced?
- what tests prove it?

## Consistency model

Be explicit about which workflows require strong consistency and which can tolerate eventual consistency.

Use immediate consistency when correctness depends on a single authoritative result.

Use eventual consistency only when the delay is acceptable and the recovery path is understood.

When eventual consistency is used, define:

- the delayed side effect
- how retries happen
- how duplicates are handled
- how operators can detect and repair failure

## Failure semantics

Design for failure before designing for reuse.

Important workflows should define:

- retry behavior
- idempotency requirements
- poison-message or permanent-failure handling
- compensation or manual recovery paths
- the audit trail for what happened

Silent failure is unacceptable for financial workflows.

## DDD and clean architecture

Use DDD-inspired structure for complex workflows.

Prefer:

- explicit use cases
- value objects for constrained concepts
- aggregates or aggregate-like boundaries when consistency matters
- domain services for business rules that do not belong on a single model
- repository abstractions only at true persistence or integration boundaries

Use hexagonal or clean-architecture ideas selectively.

Protect real seams such as:

- third-party providers
- persistence strategies that may change
- message or webhook integrations
- AI providers
- domain services that must stay framework-independent

Do not add interfaces, layers, or indirection by habit.

## Simple first, extensible later

Start with the simplest implementation that preserves important constraints.

Add abstraction when it buys something:

- clearer testing
- replaceable provider
- explicit domain boundary
- reduced duplication
- improved safety

Prefer deleting the wrong abstraction over defending it.

Do not add interfaces for every class by default.

Prefer small, intention-revealing objects and methods.

Use SOLID ideas as pressure toward better boundaries, not as a reason to over-engineer.

## Architecture decisions

Record an ADR when a choice affects:

- module boundaries
- persistence or integration strategy
- consistency model
- public or external contracts
- AI safety posture
- deployment or operational strategy

Prefer short ADRs with the decision, context, alternatives, and consequences.

## Test important behavior

Test behavior, not implementation.

Prefer tests that exercise use cases, domain rules, and observable outcomes over tests coupled to internal method structure.

Especially test:

- authorization
- money calculations
- idempotency
- tenant isolation
- background jobs
- AI safety boundaries
- external-provider handling

Use the least expensive test type that can prove the behavior confidently.

## Change in small safe slices

Prefer small reversible changes over large refactors.

Use seams, adapters, and incremental data migration to change architecture without stopping delivery.
