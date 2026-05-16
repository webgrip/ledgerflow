# ADR 001: PostgreSQL as the primary database

## Status
Accepted

## Context
LedgerFlow requires a relational database that can handle financial data with strong consistency guarantees, complex queries across organizations and accounts, and future support for features like partial indexes and advisory locks.

## Decision
Use PostgreSQL 17 as the only supported database. SQLite will not be supported.

## Rationale
- Financial data benefits from PostgreSQL's ACID guarantees and rich transaction support
- `ILIKE` is used for case-insensitive search (webhook event types, transaction descriptions)
- Future features (tenant isolation, row-level security, advisory locks for idempotency) are PostgreSQL-specific
- Laravel Sail ships PostgreSQL out of the box, keeping local dev simple

## Alternatives considered
### SQLite
Simple for testing but lacks `ILIKE`, `FOR UPDATE`, advisory locks, and production-grade concurrency.

### MySQL
Viable but lacks some PostgreSQL-specific features we rely on. No team preference for MySQL.

## Consequences
- Tests must run against PostgreSQL — CI is configured with a `postgres:17` service container
- `DB_CONNECTION=pgsql` is required; no SQLite fallback
