# ADR 001: PostgreSQL as the primary database

## Status

Accepted

## Context

LedgerFlow requires a relational database that can handle financial data with strong consistency guarantees, complex queries across organizations and accounts, and future support for features like partial indexes and advisory locks.

## Decision

Use PostgreSQL 17 as the only supported database. SQLite will not be supported.

## Rationale

- Financial data benefits from PostgreSQL's ACID guarantees and rich transaction support.
- `ILIKE` is used for case-insensitive search.
- Future features such as tenant isolation, row-level security, and advisory locks are PostgreSQL-specific.
- Laravel Sail ships PostgreSQL out of the box, keeping local development simple.

## Consequences

- Tests must run against PostgreSQL.
- `DB_CONNECTION=pgsql` is required; there is no SQLite fallback.
