---
mode: agent
description: Audit a file or module for domain/framework leakage and naming drift.
---

Audit boundaries and naming for a target path.

Inputs:

- ${input:target:Path to audit (file, directory, or domain module — e.g. app/Domain/Ledger)}

Steps:

1. Read [.ai/skills/architecture-boundaries/SKILL.md](../../.ai/skills/architecture-boundaries/SKILL.md) and [.ai/guidelines/03-laravel-standards.md](../../.ai/guidelines/03-laravel-standards.md).
2. List every PHP file under `${input:target}` and its top-level imports.
3. For each file, classify it as `domain`, `application`, `infrastructure`, or `ui`.
4. Report **leakage**:
   - `Illuminate\…`, `Eloquent`, HTTP, queue, or framework facades imported inside `domain`
   - SQL, HTTP clients, or third-party SDKs called from `domain`
   - Domain rules implemented in controllers, Livewire components, jobs, or listeners
   - Floats used for money; primitives used where a value object exists
5. Report **naming drift** against the ubiquitous language:
   - generic words: `Manager`, `Helper`, `Util`, `Data`, `Service` without a verb
   - inconsistent terms for the same concept across files
6. Suggest concrete renames and moves (not refactors). One bullet per change: `app/Foo.php:42 — rename Manager → RecordSettlement`.
7. End with a **boundary score**: count of leaks per file, total leaks, top 3 files to fix first.

Do not modify files. This is an audit.
