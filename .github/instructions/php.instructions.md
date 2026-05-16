---
applyTo: "app/**/*.php"
---

# PHP / Laravel application code

Apply silently to any edit under `app/`.

- Use PHP 8.4 constructor property promotion. Always declare parameter and return types.
- Use curly braces for every control structure, even single-line.
- Use `declare(strict_types=1);` at the top of new PHP files.
- Prefer PHPDoc array shapes over loose `array` types.
- Domain logic lives under `app/Domain/{Name}/…` — see [.ai/guidelines/03-laravel-standards.md](../../.ai/guidelines/03-laravel-standards.md). Do not import `Illuminate\…` inside `app/Domain/**`.
- Controllers, Livewire components, jobs, and listeners stay thin. Push rules into Actions, domain Services, ValueObjects, and Aggregates.
- Wrap multi-write operations in `DB::transaction(...)`. Side effects (events, jobs, notifications) must dispatch **after commit**, not inside the transaction.
- Use Eloquent at the application/persistence boundary only. Repositories or query classes belong on the domain side; do not leak query builders into domain code.
- No floats for money. Use minor units or a `Money` value object. No naked currency strings.
- Idempotency for any handler that can be retried (jobs, webhooks, imports): require an explicit idempotency key and assert it in tests.
- For new files, run `vendor/bin/sail bin pint --dirty --format agent` before considering the change done.
