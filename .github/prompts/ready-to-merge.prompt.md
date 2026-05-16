---
mode: agent
description: Run the project's pre-merge checks and report a single go/no-go verdict.
---

Run the standard pre-merge gauntlet and report.

Steps (in order; do not stop on the first failure — collect all results):

1. **Formatting:** `vendor/bin/sail bin pint --dirty --format agent`
2. **Tests:** `vendor/bin/sail artisan test --compact`
3. **Routes sanity:** `vendor/bin/sail artisan route:list --except-vendor` — flag any route without a name or controller.
4. **Config sanity:** `vendor/bin/sail artisan config:show app.env` — must not be `production` locally.
5. **Migration status:** `vendor/bin/sail artisan migrate:status` — flag any pending migration.
6. **Diff hygiene:** ensure `git status` is clean of stray files; flag any `.env`, `*.dec.yaml`, secret-looking, or binary files in the diff.
7. **Boost guidelines drift:** if available, run `vendor/bin/sail artisan boost:install --dry-run` and report whether the Boost block in AGENTS.md would change.

Output:

- One **verdict**: GO / NO-GO.
- A table: check | status | one-line note.
- If NO-GO, list the **smallest set** of follow-up actions to flip each failing check to GO.

Do not auto-fix without asking. If pint surfaces fixes, ask before running it without `--test`.
