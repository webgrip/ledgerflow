# LedgerFlow Roadmap

This roadmap defines the complete 30-day, 60-day, 90-day, and 1-year plan.

The project should evolve from a focused Laravel interview-prep app into a portfolio-grade fintech/AI platform.

## Strategy

Do not try to build everything at once.

Each phase should produce a working application that can be demonstrated.

Use this roadmap to decide what to build next and what to defer.

---

# 30-Day Plan: Foundation and Interview-Ready MVP

## Goal

Create a working Laravel fintech foundation that demonstrates modern Laravel competence.

## Main outcome

A user can sign in, create or join an organization, create financial accounts, record basic transactions, view balances/activity, and request a simple AI explanation.

## Product scope

Build:

- authentication
- user profile basics
- organization/workspace model
- organization membership
- account creation
- basic transaction recording
- transaction list
- account balance display
- audit event basics
- first AI explanation feature
- README and architecture notes

## Engineering scope

Set up:

- Laravel starter kit
- PostgreSQL
- Redis
- Sail/Docker local workflow
- Pest
- Pint
- PHPStan/Larastan
- Laravel Boost
- basic CI

## AI scope

Install and configure:

- Laravel Boost for development assistance
- project guidelines and role files
- Laravel AI SDK for first user-facing AI feature

First AI feature:

- explain a transaction or account activity in plain language

## Testing scope

Test:

- auth-protected pages
- organization access
- account creation
- basic transaction creation
- balance calculation
- AI explanation authorization
- important validation failures

## Documentation scope

Write:

- README
- local setup guide
- architecture overview
- roadmap
- first ADRs

## Demo story

The demo should show:

1. user logs in
2. user opens an organization
3. user creates accounts
4. user records transactions
5. user sees account activity
6. user asks AI to explain a transaction
7. tests and CI prove the basics

## Done criteria

The 30-day phase is done when the app can be cloned, run, tested, and demonstrated in under 10 minutes.

---

# 60-Day Plan: Serious Fintech MVP

## Goal

Move from basic app to credible fintech workflow system.

## Main outcome

The app can ingest external events, process them idempotently, run reconciliation, and show useful operational state.

## Product scope

Add:

- payment-provider abstraction
- webhook event storage
- idempotent webhook processing
- transaction import
- reconciliation runs
- reconciliation issue list
- account statements
- transaction filters/search
- richer audit log
- role-based organization permissions

## Engineering scope

Add:

- queued jobs
- Horizon
- scheduled reconciliation
- failed-job handling
- better database indexes
- domain events
- notifications where useful
- policy coverage

## AI scope

Add AI features:

- transaction classification
- reconciliation explanation
- account activity summary

AI should remain advisory.

## Testing scope

Test:

- webhook signature handling or placeholder strategy
- duplicate webhook idempotency
- queued processing
- reconciliation happy path
- reconciliation mismatch path
- organization role permissions
- AI classification with fakes

## Documentation scope

Add:

- webhook flow doc
- reconciliation flow doc
- audit model doc
- AI feature doc
- operational notes for queues

## Demo story

The demo should show:

1. external webhook arrives
2. duplicate webhook is ignored
3. transaction appears in account activity
4. reconciliation run detects or resolves issues
5. AI explains a mismatch
6. Horizon shows queue processing

## Done criteria

The 60-day phase is done when the app demonstrates real async workflows and safe external-event handling.

---

# 90-Day Plan: Production-Shaped Platform

## Goal

Make the app look like something a serious Laravel team could evolve.

## Main outcome

The app has stronger security, observability, AI governance, and external read-only AI access.

## Product scope

Add:

- audit log viewer
- export workflow
- signed export links
- admin/operations dashboard
- reconciliation status dashboard
- AI analysis history
- settings for AI usage
- read-only MCP tools

## Engineering scope

Add:

- structured logging
- Pulse dashboard
- OpenTelemetry design or initial instrumentation
- rate limits
- health checks
- stronger CI
- seed/demo data
- safer migration strategy
- operational runbooks

## AI scope

Add:

- document/statement Q&A
- AI analysis history
- read-only MCP server
- tool-level authorization
- audit logging for AI/MCP calls

## Security scope

Review:

- tenant isolation
- export access
- AI prompt data
- webhook validation
- audit coverage
- rate limits
- sensitive logging
- dependency risk

## Testing scope

Test:

- MCP read-only tools
- export authorization
- audit visibility
- AI prompt scoping
- rate-limited endpoints
- cross-tenant access attempts
- failed queued jobs where practical

## Documentation scope

Add:

- security notes
- observability guide
- MCP guide
- AI safety guide
- deployment guide
- demo script

## Demo story

The demo should show:

1. operator dashboard
2. reconciliation issue
3. AI explanation
4. read-only MCP query
5. audit trail
6. queue and app metrics
7. security controls

## Done criteria

The 90-day phase is done when the app has credible production shape, even if not fully production complete.

---

# 1-Year Plan: Portfolio-Grade Fintech/AI Platform

## Goal

Turn LedgerFlow into a deep technical portfolio platform.

## Main outcome

The app demonstrates Laravel, fintech modeling, AI, observability, security, deployment, and long-term architectural thinking.

## Product capabilities

Potential features:

- multi-currency accounts
- FX conversion model
- account periods
- transaction reversals
- holds/reservations
- approval workflows
- chargebacks/disputes
- risk scoring
- statement imports
- provider sandbox
- developer API
- reporting dashboard
- cashflow projection
- AI support assistant
- AI fraud triage
- document Q&A
- read-only external MCP tools

## Engineering capabilities

Potential additions:

- public API
- JSON:API resources
- OAuth/API token strategy
- outbox pattern
- event-driven projections
- background import pipeline
- advanced search
- caching strategy
- load testing
- backup/restore testing
- Kubernetes/Helm deployment
- reusable GitHub Actions workflows

## AI capabilities

Potential additions:

- merchant normalization
- transaction categorization
- reconciliation analyst
- support response drafting
- statement Q&A
- policy Q&A
- anomaly explanation
- fraud triage assistant
- natural-language search
- AI cost tracking
- AI governance dashboard

## Platform capabilities

Potential additions:

- Kubernetes deployment
- Helm chart
- SOPS/Akeyless/Vault-style secrets
- Grafana/Loki/Tempo/Prometheus
- OpenTelemetry traces
- CI/CD pipeline
- preview environments
- release notes
- rollback process
- operational runbooks

## Documentation capabilities

Create:

- polished README
- architecture diagrams
- ADR collection
- threat model
- AI safety document
- deployment guide
- demo videos or screenshots
- API docs
- MCP docs
- roadmap history
- interview talking points

## Done criteria

The 1-year phase is done when the project can be used as a serious proof of capability for Laravel, backend engineering, AI integration, and production-platform thinking.
