# Architecture Overview

LedgerFlow is a modular Laravel monolith.

## Why modular monolith?

This keeps the app:

- easy to run locally
- easy to test
- easy to deploy
- transactionally simple
- understandable for interviews and portfolio review

It still allows clear boundaries between domains.

## Main domains

- Identity
- Organizations
- Accounts
- Transactions/Ledger
- Payments
- Reconciliation
- Audit
- AI
- Reporting
- Operations

## Request flow

```text
request
  -> middleware
  -> controller/livewire component
  -> authorization
  -> validation
  -> action/service
  -> model/database
  -> events/jobs
  -> response
```

## External event flow

```text
provider event
  -> webhook endpoint
  -> validation/signature check
  -> idempotency check
  -> store event
  -> queued job
  -> domain action
  -> audit log
```

## AI flow

```text
user asks for AI help
  -> authorization
  -> context collection
  -> redaction/minimization
  -> AI agent call
  -> structured/advisory output
  -> audit event
  -> display result
```
