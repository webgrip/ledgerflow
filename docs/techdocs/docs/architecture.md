# Architecture

LedgerFlow is a modular Laravel monolith. It uses Laravel's batteries-included runtime while keeping domain boundaries explicit through actions, policies, jobs, and events.

## Why modular monolith

This keeps the system easy to run, test, and deploy while preserving transactional consistency for financial workflows. Service extraction should only happen when a boundary has independent scale, ownership, or deployment needs.

## Main domains

- Identity and authentication
- Organizations and membership
- Accounts and transactions
- Reconciliation
- Payment provider webhooks
- Audit logging
- AI analysis
- Operations and observability

## Request flow

```mermaid
flowchart TD
    Request[HTTP request] --> Middleware[Middleware]
    Middleware --> Boundary[Controller or Livewire component]
    Boundary --> Auth[Authorization]
    Auth --> Validation[Validation]
    Validation --> Action[Domain action or service]
    Action --> Model[Eloquent model and database]
    Action --> Events[Events and jobs]
    Events --> Response[Response]
    Model --> Response
```

## External event flow

```mermaid
flowchart TD
    Provider[Provider event] --> Endpoint[Webhook endpoint]
    Endpoint --> Signature[Signature validation]
    Signature --> Idempotency[Idempotency check]
    Idempotency --> Stored[Store provider event]
    Stored --> Job[Queued job]
    Job --> Domain[Domain action]
    Domain --> Audit[Audit event]
```

## AI flow

```mermaid
flowchart TD
    User[User asks for AI help] --> Authorize[Authorize]
    Authorize --> Context[Collect scoped context]
    Context --> Minimize[Redact and minimize]
    Minimize --> Agent[Laravel AI SDK agent]
    Agent --> Output[Structured or advisory output]
    Output --> Audit[Audit event]
    Output --> UI[Display result]
```

## Boundary rules

- Controllers, Livewire components, jobs, and commands should be thin.
- Domain actions own state-changing workflows.
- Policies own access decisions.
- Jobs own asynchronous retries and failure handling.
- Audit events describe meaningful business and operational activity.
