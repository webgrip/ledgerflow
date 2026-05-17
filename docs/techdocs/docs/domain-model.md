# Domain Model

LedgerFlow is organized around financial ownership, transaction integrity, and auditable side effects.

## Core concepts

| Concept | Responsibility |
| --- | --- |
| User | Authenticated actor managed by Laravel Fortify. |
| Organization | Tenant boundary for accounts, transactions, reconciliation, and MCP tools. |
| Account | Financial bucket with type, currency, and computed running balance. |
| Transaction | Credit or debit entry stored in minor units. |
| Reconciliation Run | Date-range analysis over account activity and detected issues. |
| Webhook Event | Provider event envelope with idempotency and processing status. |
| Audit Event | Immutable record of important user, system, webhook, and AI activity. |
| AI Call | Audited interaction with a Laravel AI SDK agent. |

## Tenant boundary

Organizations are the primary data boundary. Policies and query scopes must prevent cross-organization reads and writes. MCP tools are read-only and automatically scoped to the authenticated user's current organization.

## Money rules

- Store all amounts as integer minor units.
- Keep currency explicit on accounts and any derived financial output.
- Do not use floats for persisted or calculated money.
- Prefer domain actions for mutations so validation, authorization, auditing, and side effects stay consistent.

## Event ownership

Webhook events are stored before processing so delivery, deduplication, retries, and failures remain auditable. Audit events are append-only and should describe what happened, who or what caused it, and which subject was affected.
