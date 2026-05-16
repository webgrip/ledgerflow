# Laravel Standards

## Laravel first

Use Laravel conventions before inventing architecture.

Prefer:

- Eloquent models
- migrations
- factories
- seeders
- policies
- form requests
- jobs
- events/listeners
- notifications
- Livewire components
- service container
- config files
- tests

## Application structure

Use standard Laravel folders unless a module boundary is useful.

Acceptable structure:

```text
app/
  Actions/
  Domain/
  Http/
  Livewire/
  Jobs/
  Listeners/
  Models/
  Policies/
  Providers/
```

For complex domains, use:

```text
app/Domain/{DomainName}/
  Actions/
  Aggregates/
  Data/
  Events/
  Exceptions/
  Models/
  Ports/
  Policies/
  Queries/
  Rules/
  Services/
  ValueObjects/
```

Use only the folders that pay for themselves.

Do not create empty architectural layers to satisfy a pattern.

## Application and domain boundaries

Treat these as application or adapter entry points:

- controllers
- Livewire components
- console commands
- jobs
- listeners
- notifications

These layers should coordinate work, not own business rules.

Prefer domain or action classes for:

- financial calculations
- reconciliation decisions
- invariant enforcement
- status transitions
- provider-independent business workflows

When using ports and adapters, keep framework and vendor details at the edge.

The domain should not need to know about HTTP requests, queue payloads, controller concerns, or vendor SDK response objects unless there is a concrete reason.

## Transactions and after-commit behavior

Define the transaction boundary for every write-heavy use case.

When a workflow changes state and also emits external side effects, persist first and dispatch side effects after commit.

Prefer `afterCommit` semantics for jobs, notifications, listeners, and external publishing that must not observe rolled-back state.

## Domain events and integration events

Treat internal domain events and external integration events as different concerns.

Internal domain events help coordinate application behavior.

External events, webhooks, exports, and provider notifications are contracts and need stronger durability, idempotency, and versioning discipline.

For important external side effects, prefer an outbox-style pattern over ad hoc save-then-publish code.

## Concurrency and idempotency

Be explicit about concurrency control.

Use the simplest mechanism that protects correctness:

- database transactions
- unique constraints
- optimistic version checks
- row locks when justified
- atomic locks for coarse application coordination
- unique or deduplicated jobs when retries can overlap

Do not rely on best-effort timing or queue order for correctness.

## Controllers

Controllers should be thin.

They should:

- authorize
- validate
- call actions/services
- return responses

They should not contain complex business logic.

## Actions

Use action classes for meaningful operations.

Actions should model use cases and orchestrate collaborators.

They may call domain services, repositories, policies, or integrations, but should avoid becoming generic utility buckets.

Actions should name the business outcome they produce.

Examples:

- CreateOrganization
- InviteOrganizationMember
- CreateAccount
- ImportTransactions
- ProcessWebhook
- RunReconciliation
- GenerateAiExplanation

## Query and reporting separation

Use a light CQRS split when it improves clarity.

Write-side actions should protect invariants and state transitions.

Read-side queries, dashboards, exports, and reports may use optimized query code or projections when useful.

Do not force the same object model to serve every write and reporting need.

## Domain modeling

Prefer value objects for concepts with rules, formatting, or invariants.

Examples:

- money amounts
- currency pairs
- statement periods
- external reference identifiers

Keep validation close to the domain rule that requires it.

Use Eloquent for persistence, but do not let database shape fully dictate domain language.

For important lifecycle changes, prefer explicit transition methods or transition policies over scattered status string updates.

## Eloquent discipline

Use Eloquent intentionally.

Avoid hiding critical business behavior inside model boot hooks, attribute side effects, or observer magic when an action or domain service would be clearer.

Prevent N+1 problems and ambiguous loading behavior.

Prefer explicit eager loading for important paths.

## Validation

Use form requests for HTTP validation.

Use custom validation rules for reusable domain validation.

## Authorization

Use policies for model access.

Every organization-scoped resource must have an authorization strategy.

## Tenant context propagation

When work leaves the request boundary, preserve tenant and actor context deliberately.

Jobs, events, exports, AI requests, and audit events should retain enough context to enforce authorization, logging, and repairability.

## Queues

Use jobs for slow or retryable work:

- webhook processing
- statement import
- AI analysis
- reconciliation
- export generation
- notifications

Jobs should be idempotent when retries are possible.

Use dead-letter or failure handling that leaves operators enough information to recover.

## Formatting and analysis

Prefer:

```bash
vendor/bin/pint
vendor/bin/pint --test
vendor/bin/phpstan analyse
php artisan test
```

Agents should not ignore style or static-analysis failures without documenting why.
