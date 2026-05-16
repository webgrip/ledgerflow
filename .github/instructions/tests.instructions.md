---
applyTo: "tests/**/*.php"
---

# Test code

Apply silently to anything under `tests/`.

- Use Pest syntax (`it()`, `test()`, `expect()`). No PHPUnit class-style tests in new code.
- Test names describe **observable behavior**, not method names: `it('rejects duplicate webhook deliveries')`, not `it('tests handle method')`.
- Prefer feature tests for entry points and integration boundaries. Use unit tests for value objects, pure calculations, and domain services.
- Use factories (and factory states) over hand-built models. Add a state to the factory instead of repeating setup across tests.
- For any change that can be retried (jobs, webhooks, imports), include a duplicate-delivery test.
- For any authorization rule, include both an allowed and a denied case.
- Do not assert on private methods, internal SQL, or framework internals.
- Use `RefreshDatabase` (or the project's standard trait) for any test that touches the database.
- Faker: follow whichever style the neighboring file uses (`$this->faker` or `fake()`); do not mix.
- Never delete an existing test without explicit approval. If a test is wrong, fix it; if it is obsolete, ask.
