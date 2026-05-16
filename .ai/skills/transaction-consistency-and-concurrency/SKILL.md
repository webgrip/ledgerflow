---
name: transaction-consistency-and-concurrency
description: Use when designing or reviewing transactions, after-commit behavior, locking, idempotency keys, duplicate delivery, race conditions, unique jobs, or consistency-sensitive workflows.
---

# Transaction Consistency and Concurrency Skill

## Use this when

- a use case writes multiple related records
- a workflow emits side effects after persistence
- the same job or webhook might arrive twice
- concurrent updates could corrupt state
- queue timing is being used implicitly for correctness

## Checklist

- is the transaction boundary explicit?
- are side effects dispatched only after committed state is durable?
- is idempotency designed with stable keys or duplicate detection?
- is the concurrency control mechanism explicit and minimal?
- are retry and permanent-failure paths defined?
- do tests cover duplicates, retries, or race-prone behavior where relevant?
