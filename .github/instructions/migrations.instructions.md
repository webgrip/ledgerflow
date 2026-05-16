---
applyTo: "database/migrations/**/*.php"
---

# Database migrations

Apply silently to any migration edit.

- Migrations are **append-only** in shared environments. Never edit a migration that has already shipped; write a new one.
- Schema changes that affect running code must follow expand → backfill → contract:
  1. **Expand** — add nullable columns, new tables, new indexes (online).
  2. **Backfill** — populate via a queued job, in batches, idempotent.
  3. **Contract** — drop old columns / constraints only after the writers and readers are gone.
- Every new column gets an explicit type, nullability, default, and (where appropriate) index decision documented in the migration.
- Add indexes for foreign keys and for columns used in `where`, `order by`, or `unique` constraints.
- Money columns store minor units as `bigInteger` (or `decimal` with explicit scale) — never `float` or `double`.
- Timestamps for audit-relevant events should be `timestampTz` (UTC) and immutable once written.
- Avoid `dropColumn` or `renameColumn` on tables that other services or replicas read. Coordinate via ADR if you must.
- Include a working `down()` only if rollback is genuinely safe; otherwise throw and explain why.
