---
mode: agent
description: Propose the missing test cases for the current change, then optionally write them.
---

Find the test gap on the current branch.

Steps:

1. Get the diff: `git diff origin/main...HEAD --name-only` and inspect changed production files.
2. Read [.ai/skills/testing-strategy/SKILL.md](../../.ai/skills/testing-strategy/SKILL.md) and apply its heuristics.
3. For each changed production file, list:
   - **Behaviors introduced** (one bullet per observable behavior)
   - **Existing test coverage** (filename + test name, or "none")
   - **Missing cases** — group as: happy path, authorization, validation, idempotency / duplicate delivery, edge / boundary, failure / rollback, observability
4. Rank the missing cases by risk (impact × likelihood). Mark the top 3 as **must-add-before-merge**.
5. Ask the user: "Write the must-add tests now?" If yes:
   - Create them under `tests/Feature/...` or `tests/Unit/...` matching the project's existing layout.
   - Use factories and Pest syntax.
   - Run `vendor/bin/sail artisan test --compact --filter=<new test name>` and report results.
6. Do not delete or rewrite existing tests. If an existing test seems wrong, flag it instead.
