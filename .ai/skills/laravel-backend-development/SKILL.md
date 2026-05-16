---
name: laravel-backend-development
description: Use when implementing Laravel backend features, including migrations, models, actions, jobs, controllers, policies, events, queries, or domain workflows.
---

# Laravel Backend Development Skill

## Use this when

- adding backend features
- writing migrations
- creating Eloquent models
- implementing jobs/events/listeners
- adding controllers or API endpoints
- writing policies
- introducing a new domain workflow

## Process

1. Inspect existing conventions.
2. Identify affected models and routes.
3. Define the write path, read path, and transaction boundary.
4. Add validation and authorization.
5. Implement business logic in actions, domain services, or value objects where useful.
6. Decide how side effects are dispatched and retried.
7. Add tests.
8. Format code.
9. Update docs if needed.

## Checklist

- validation exists
- authorization exists
- organization scoping is respected
- transaction boundary is explicit where correctness depends on it
- side effects are safe with retries and duplicates
- tests cover happy and important failure paths
- code is formatted
- no unnecessary abstraction added
