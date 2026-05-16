---
mode: agent
description: Drive a Pest TDD loop (red → green → refactor) for a described behavior.
---

Drive a strict TDD loop for one behavior.

Inputs:

- ${input:behavior:Describe the behavior in one sentence — what should be true after the change?}

Steps:

1. Read [.ai/skills/testing-strategy/SKILL.md](../../.ai/skills/testing-strategy/SKILL.md).
2. **RED** — write one Pest test that expresses `${input:behavior}` and currently fails. Place it under `tests/Feature/...` (or `tests/Unit/...` if the behavior is pure). Use the project's factories. Run only this test: `vendor/bin/sail artisan test --compact --filter='<test name>'`. Confirm it fails for the right reason (not a syntax error).
3. **GREEN** — make the smallest production change that turns the test green. No extra features. No speculative interfaces. Run the same filter and confirm pass.
4. **REFACTOR** — if and only if duplication or unclear naming appeared, refactor in tiny steps. Re-run the test after each step.
5. After the loop, run the full file's tests once: `vendor/bin/sail artisan test --compact <test file>`.
6. Report:
   - the failing assertion that proved red
   - the production diff that produced green
   - any refactor steps applied
   - any further behaviors the user might want to add next (numbered list)

Rules:

- Never skip the red step. If you cannot make it fail first, the test is wrong.
- Do not add unrelated tests in the same loop. One behavior per loop.
- Do not edit existing tests except to remove them with explicit approval.
