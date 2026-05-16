---
mode: agent
description: Analyze the current diff for boundary crossings, missing tests, and rollout risk.
---

Produce a Change Impact Report for the current branch's diff.

Steps:

1. Run `git diff --stat origin/main...HEAD` and `git diff origin/main...HEAD` to ground every finding in real changes. If there is no diff vs `main`, stop and ask for a target ref.
2. Classify each changed file into one of: `domain`, `application`, `infrastructure`, `ui`, `test`, `config`, `docs`, `ops`. Use [.ai/guidelines/03-laravel-standards.md](../../.ai/guidelines/03-laravel-standards.md) for the layering definition.
3. Flag any **boundary crossings** that violate layering (e.g. `app/Domain/**` importing `Illuminate\…`, controllers containing business rules, raw SQL in domain code, floats touching money).
4. Identify **invariants at risk**: transactions, idempotency keys, after-commit dispatch, authorization checks, tenant scoping. For each, state whether the diff preserves, weakens, or strengthens it.
5. Compute a **test coverage delta**: which production files changed without a corresponding test change?
6. Decide whether this change needs:
   - an ADR (use the [.ai/guidelines/07-change-safety-and-delivery.md](../../.ai/guidelines/07-change-safety-and-delivery.md) triggers)
   - a migration with expand/backfill/contract
   - a new or updated contract test
   - a rollout plan / feature flag
7. Output a single markdown report with sections: **Summary**, **Boundary findings**, **Invariants**, **Test gap**, **Required artifacts**, **Suggested next commits**.

Do not modify any files. This is an analysis-only prompt.
