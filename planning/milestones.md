# Milestones

Milestones are cumulative. Each one produces a demonstrable, working state.
✅ = complete · 🔄 = in progress · 📋 = planned

---

## ✅ M0: Project Initialized

Deliverables:

- Laravel app created
- starter kit installed
- database (PostgreSQL) configured
- tests running (Pest)
- Boost, Pint, PHPStan installed
- planning docs committed

---

## ✅ M1: Authenticated Workspace

Deliverables:

- users can register/login (Fortify)
- organizations exist with owner/member roles
- users belong to organizations
- role-based authorization exists (AccountPolicy, TransactionPolicy)
- audit log records all mutations

---

## ✅ M2: Account Management

Deliverables:

- accounts can be created (Asset, Liability, Revenue, Expense)
- accounts belong to organizations
- account list/detail views exist (Livewire + Flux UI)
- policies protect accounts (owner-only create/update/delete)
- CSV export of transactions
- CSV import of transactions (modal, validation, deduplication)

---

## ✅ M3: Transaction Foundation

Deliverables:

- transactions can be recorded (credit/debit)
- transaction list with URL-bound filters (search, type, date range)
- account balance calculated from transaction history
- basic validation exists
- tests cover core behavior

---

## ✅ M4: AI Explanation

Deliverables:

- Laravel AI SDK installed
- TransactionExplainer agent (text)
- user can request explanation per transaction
- AI context scoped to org + account
- AI response is advisory only
- tests use AI fakes

---

## ✅ M5: Webhook Ingestion

Deliverables:

- provider_events table with idempotency_key
- POST /webhooks/{provider} endpoint
- Stripe HMAC-SHA256 signature validation
- idempotency protection (two-layer: controller + job)
- queued ProcessWebhookEvent job
- webhook replay tool (owner-only)
- duplicate handling tests
- WebhookSignatureTest

---

## ✅ M6: Reconciliation

Deliverables:

- reconciliation_runs + reconciliation_issues tables
- RunReconciliation action
- reconciliation dashboard/list/show pages
- AI analyst explanation for issues (ReconciliationAnalyst)
- TransactionCategorizer (structured output)
- AccountActivitySummarizer

---

## ✅ M7: Operations

Deliverables:

- Laravel Horizon (queue dashboard)
- Laravel Pulse (performance monitoring)
- Laravel Telescope (local dev debugging)
- structured JSON logging channel (LOG_CHANNEL=json)
- GET /health endpoint (DB + cache + queue checks)
- Pulse, Horizon, Telescope, Health links in sidebar

---

## ✅ M8: Read-Only MCP

Deliverables:

- LedgerFlowServer with 4 org-scoped tools
- get-account-summary, search-transactions, list-reconciliation-issues, list-audit-events
- auth middleware on /mcp/ledgerflow
- AI cost tracking in audit_events (AuditLogger::logAiCall)
- 13 tests covering all tools + auth enforcement

---

## ✅ M9: Portfolio Polish (30-day complete)

Deliverables:

- comprehensive README with tech stack, quick start, demo credentials, MCP docs
- 4 ADRs (PostgreSQL, Livewire+Flux, Laravel AI SDK, idempotent webhooks)
- CI workflow (GitHub Actions): PostgreSQL 17 + Redis 7, Pint + PHPStan + Pest
- e2e:seed command with full demo data
- Playwright E2E suite (102 tests)
- audit log page (/audit-log)
- webhooks page (/webhooks) with replay
- dev dashboard (/dev)

---

## ✅ M10: Security Hardening (P4 complete)

Deliverables:

- named rate limiters: api (60/min), webhooks (120/min), ai (10/min)
- tenant isolation tests (cross-org 403 for accounts, transactions, export)
- webhook HMAC signature validation with config/webhooks.php
- CSV export authorization (view policy)
- CSV import with deduplication

---

## ✅ M11: Advanced Test Coverage (P5 complete)

Deliverables:

- manual .http files (tests/manual/ledgerflow.http)
- Gherkin .feature files (tests/behavioral/*.feature — 6 flows)
- k6 performance script (tests/performance/load.js — 3 scenarios)
- complete test pyramid: Unit, Integration, Functional, Contract, Smoke, E2E, Behavioral, Performance

---

## 📋 M12: Supply Chain & Developer Standards

Priority: High (quick wins, professional signal)

Deliverables:

- `composer audit` step added to CI
- CycloneDX SBOM generation (`bom.xml`) added to CI
- `.editorconfig` for cross-editor consistency
- `SECURITY.md` responsible disclosure policy
- `CHANGELOG.md` (conventional commits → changelog via `git-cliff` or manual)
- AI safety document (`docs/ai-safety.md`)
- PHPStan level bump (5 → 6 or 7)
- test coverage report in CI (`--coverage`, Clover XML)
- `commitlint` or similar for commit message linting

---

## 📋 M13: Public REST API

Priority: High (turns LedgerFlow into a real SaaS product)

Deliverables:

- Laravel Sanctum API token auth
- versioned JSON:API routes under `/api/v1/`
- API resources: OrganizationResource, AccountResource, TransactionResource, ReconciliationRunResource
- API policies (same RBAC as web, but token-scoped)
- OpenAPI specification (`docs/openapi.yaml`)
- API tests (Feature + Contract)
- API rate limiting (via `api` named limiter)
- API docs in README

---

## 📋 M14: AI Depth — Embeddings, RAG, and Conversational

Priority: Medium (impressive demo features)

Deliverables:

- **Semantic transaction search** — `Embeddings::for(descriptions)->generate()`, cosine similarity query
- **Conversational reconciliation assistant** — `RemembersConversations` trait, persistent chat per reconciliation run
- **Document Q&A / RAG** — upload PDF bank statement → `Document::fromPath()->put()` → `Stores`, ask questions via `FileSearch` provider tool
- **Agent with MCP tools** — AI agent that calls `get-account-summary` tool internally to answer questions
- **Streaming AI responses** — character-by-character explanation in the browser
- **AI broadcasting** — push queued AI job result to browser when done
- **AI governance dashboard** — costs per agent, per org, per day (from audit_events where event = 'ai.agent_called')

---

## 📋 M15: Fintech Domain Depth

Priority: Medium (domain credibility)

Deliverables:

- **Transaction reversals** — create a correction/reversal entry that negates a previous transaction; linked by `reversal_of_id`
- **Multi-currency accounts** — store `currency` per account; minor-unit amount + currency code on transactions
- **FX conversion notes** — ADR + model design (no live rates needed, just design)
- **Account periods** — mark a period as closed; prevent transaction backdating
- **Cashflow projection agent** — AI forecasts 30/60/90-day cashflow from transaction history (`#[UseSmartestModel]`)
- **Merchant normalization** — AI normalizes raw descriptions ("AMZN MKTP US" → "Amazon") using embeddings or structured output
- **Approval workflows** — high-value transactions require owner approval before posting

---

## 📋 M16: Observability & Deployment

Priority: Medium (production-shaped)

Deliverables:

- **OpenTelemetry** — install `open-telemetry/opentelemetry-auto-laravel`, emit traces to OTLP endpoint; design doc
- **Grafana / Loki / Tempo notes** — `docs/observability.md` with recommended stack for LedgerFlow
- **Laravel Cloud deployment** — `laravel.yml`, deployment guide in `docs/deployment.md`
- **Kubernetes / Helm design** — `docs/kubernetes.md` (architecture notes; no running cluster needed)
- **Runbooks** — `docs/runbooks/` — deploy, rollback, queue drain, DB backup
- **SOPS / Vault secrets** — ADR on secrets management strategy
- **Preview environments** — GitHub Actions workflow for PR-based preview deploys (design)
