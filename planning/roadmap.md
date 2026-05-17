# LedgerFlow Roadmap

This roadmap defines the complete 30-day, 60-day, 90-day, and 1-year+ plan.

The project evolves from a focused Laravel interview-prep app into a portfolio-grade fintech/AI platform.

## Progress

| Phase | Milestones | Status |
|-------|-----------|--------|
| 30-day MVP | M0–M4 | ✅ Complete |
| 60-day Fintech | M5–M6 | ✅ Complete |
| 90-day Production Shape | M7–M11 | ✅ Complete |
| Standards & API | M12–M13 | 📋 Next |
| AI Depth | M14 | 📋 Planned |
| Fintech Domain | M15 | 📋 Planned |
| Observability & Deploy | M16 | 📋 Planned |

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

---

# Phase 4: Standards & API (M12–M13)

## Goal

Close the professional polish gaps and expose a real public API. Makes LedgerFlow look like a production SaaS.

## M12: Supply Chain & Developer Standards

Quick wins. Each item is independent and takes < 1 day.

- `composer audit` in CI (dependency vulnerability scanning)
- CycloneDX SBOM generation (`bom.xml`) — supply-chain security signal for fintech context
- `.editorconfig` — cross-editor formatting consistency
- `SECURITY.md` — responsible disclosure policy
- `CHANGELOG.md` + `git-cliff` — automated changelog from conventional commits
- `docs/ai-safety.md` — advisory-only principles, prompt minimization, no direct mutations
- `docs/threat-model.md` — OWASP-style threat model
- PHPStan level bump (5 → 6 or 7)
- test coverage report (`--coverage`, Clover XML, CI badge)

## M13: Public REST API

Turns LedgerFlow into a real SaaS product. Required for the API-first direction.

- Laravel Sanctum API token auth
- versioned JSON:API routes under `/api/v1/`
- resources: OrganizationResource, AccountResource, TransactionResource, ReconciliationRunResource
- API policies (same RBAC, token-scoped)
- OpenAPI spec (`docs/openapi.yaml`)
- API Feature + Contract tests
- API rate limiting
- ADR-005: API design decision (JSON:API vs REST vs GraphQL)

## Done criteria

M12 + M13 are done when: `composer audit` passes in CI, a CycloneDX SBOM is committed, and authenticated API clients can query accounts and transactions via `/api/v1/`.

---

# Phase 5: AI Depth (M14)

## Goal

Demonstrate the full depth of the Laravel AI SDK — not just text generation but embeddings, RAG, conversation memory, and tool use.

## M14: AI Depth — Embeddings, RAG, Conversational

- **Semantic transaction search** — `Embeddings::for(descriptions)->generate()`, cosine similarity query; replaces/supplements text filter
- **Conversational reconciliation assistant** — `RemembersConversations` trait, persistent chat per reconciliation run
- **Document Q&A / RAG** — upload PDF bank statement → `Document::fromPath()->put()` → `Stores::create()`, ask questions via `FileSearch` provider tool
- **Agent with internal tool calling** — AI agent that calls `get-account-summary` MCP tool to answer user questions autonomously
- **Streaming AI responses** — character-by-character output in the browser using `->stream()`
- **AI broadcasting** — queued AI job pushes result to browser when done (Laravel Broadcasting)
- **AI governance dashboard** — costs per agent, per org, per day (from `audit_events` where `event = 'ai.agent_called'`)
- **ADR-006**: Embeddings and semantic search strategy

## Done criteria

M14 is done when: at least one embeddings-based feature works end-to-end in tests using AI fakes, and a conversational agent with persistence is demonstrated.

---

# Phase 6: Fintech Domain Depth (M15)

## Goal

Deepen the financial domain model. Shows understanding of real fintech problems beyond basic CRUD.

## M15: Fintech Domain

- **Transaction reversals** — `reversal_of_id` FK, create negating entry, audit trail
- **Multi-currency accounts** — `currency` per account, minor-unit amounts + currency code on transactions
- **FX conversion ADR** — design document (no live rates needed; model design is the signal)
- **Account period closing** — mark period as closed, prevent backdated transactions
- **Cashflow projection agent** — AI forecasts 30/60/90-day cashflow from history (`#[UseSmartestModel]`)
- **Merchant normalization** — AI normalizes raw descriptions using structured output
- **Approval workflows** — high-value transactions require owner approval before posting
- **Reconciliation reports / export** — PDF or CSV summary of a reconciliation run

## Done criteria

M15 is done when: transaction reversals work end-to-end with tests, multi-currency accounts exist in the data model, and the cashflow projection agent returns structured forecasts.

---

# Phase 7: Observability & Deployment (M16)

## Goal

Make the app look like something a real engineering team could operate and evolve in production.

## M16: Observability & Deployment

- **OpenTelemetry** — `open-telemetry/opentelemetry-auto-laravel`, emit traces to OTLP endpoint; design doc
- **`docs/observability.md`** — recommended Grafana + Loki + Tempo + Prometheus stack for LedgerFlow
- **Laravel Cloud deployment** — `laravel.yml`, `docs/deployment.md`
- **Kubernetes / Helm design** — `docs/kubernetes.md` (architecture notes; no running cluster required)
- **`docs/runbooks/`** — deploy, rollback, queue drain, DB backup, secret rotation
- **ADR-007**: OpenTelemetry and observability stack choice
- **SOPS / Vault secrets ADR** — secrets management strategy at scale
- **Preview environments** — GitHub Actions workflow design for PR-based previews

## Done criteria

M16 is done when: OpenTelemetry is wired up (even if only to a local OTLP endpoint), a deployment guide exists, and the runbooks cover the 5 most common operational scenarios.

