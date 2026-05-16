---
mode: agent
description: Rename ambiguous symbols in a file using the project's ubiquitous language.
---

Sharpen names in a target file or directory.

Inputs:

- ${input:target:File or directory to clean up}

Steps:

1. Read [.ai/guidelines/02-engineering-principles.md](../../.ai/guidelines/02-engineering-principles.md) for the ubiquitous-language rules.
2. Scan `${input:target}` for symbols that are vague or generic: `data`, `info`, `temp`, `result`, `process`, `handle`, `manager`, `helper`, `util`, `do`, `make`, `run` (without an object), single-letter names outside tight loops.
3. For each, propose a concrete rename grounded in the domain. Format: `path:line — oldName → newName  (reason)`.
4. Detect **synonym drift**: the same concept named differently in nearby code (`account` vs `wallet`, `customer` vs `user`, `transfer` vs `movement`). Pick one and propose the change everywhere.
5. After the user approves the list, apply the renames using the language server (rename symbol), not raw find-and-replace.
6. Run `vendor/bin/sail bin pint --dirty --format agent` and `vendor/bin/sail artisan test --compact` to verify nothing broke.
7. Report the diff stats and any tests that touched the renamed symbols.

Do not invent domain terms. If unsure which term is canonical, ask.
