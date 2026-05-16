# Change Safety and Delivery

## Architectural delivery style

Prefer small, reversible changes over large rewrites.

When architecture must evolve, introduce seams first, migrate behavior second, and delete old paths last.

## ADR triggers

Write an ADR when a change affects:

- module boundaries
- event or integration strategy
- consistency model
- data retention or audit policy
- public API, webhook, export, or AI output contract
- deployment, rollout, or rollback expectations

## Schema and data evolution

Prefer expand-contract database changes.

Avoid migrations that require a perfectly synchronized deploy unless there is no safer option.

Backfills should be:

- resumable
- idempotent where practical
- observable
- safe to rerun or repair

## Contract evolution

Version external contracts deliberately.

This applies to:

- APIs
- webhooks
- exports
- AI structured outputs
- MCP tool schemas

Do not break consumers casually.

## Rollout and rollback

For risky changes, define:

- how the feature is enabled
- how it is monitored
- how it is rolled back or disabled
- what data migration or cleanup is required

Feature flags are useful when they reduce deployment risk, not when they become a permanent substitute for cleanup.

## Delivery hygiene

Prefer atomic logical changes.

If commits are created, use conventional commit style when practical.

When behavior, setup, or architecture changes, update tests and relevant documentation in the same change.
