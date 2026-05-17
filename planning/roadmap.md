# LedgerFlow Roadmap

This roadmap defines the build order from zero to portfolio-grade fintech/AI platform.
Each phase produces a demonstrable, working application.

Status key: ✅ Done · ⬜ Not started

---

## Phase 1 — Foundation & Interview-Ready MVP (30-day)
**Milestones: M0–M4 + M9 (polish)**
✅ Complete

What was built:
- Fortify auth (login, register, email verify, settings)
- Multi-org workspace with Owner/Member roles
- Account management (Asset/Liability/Revenue/Expense, currency)
- Transaction recording with balance calculation
- Transaction filters (search, type, date range — URL-bound, reactive)
- AI explanation via TransactionExplainer agent
- AuditLogger fires on every mutation and AI call
- Pest tests, PHPStan level 5, Pint, CI (GitHub Actions)
- Playwright E2E suite (102 tests)
- Dev dashboard, README, 4 ADRs, demo seed (Alice/Bob/Carol)

---

## Phase 2 — Serious Fintech Workflows (60-day)
**Milestones: M5–M8**
✅ Complete

What was built:
- Idempotent webhook ingestion with Stripe HMAC validation
- `ProcessWebhookEvent` queued job with two-layer idempotency guard
- Webhook replay tool (owner-only, failed events)
- Reconciliation runs + issues + AI mismatch explanation
- TransactionCategorizer (structured AI output)
- AccountActivitySummarizer
- Laravel Horizon, Pulse, Telescope, `/health`, structured logging
- Read-only MCP server (4 org-scoped tools, 13 tests)
- Rate limiting (api/webhooks/ai), tenant isolation tests
- CSV export + import (modal, validate, dedup)
- Audit log viewer page, Webhooks page

---

## Phase 3 — Production-Shaped Platform (90-day)
**Milestones: M10–M11**
✅ Complete

What was built:
- Security hardening: HMAC, rate limits, tenant isolation test suite
- `AuditLogger::logAiCall()` for token + model cost tracking
- `SECURITY.md`-ready patterns, webhook signature config
- Manual .http files, Gherkin .feature files, k6 performance tests
- Complete test pyramid: Unit, Integration, Functional, Contract, Smoke, E2E, Behavioral, Performance
- CI overhauled: PostgreSQL 17, Redis 7, Pint + PHPStan + Pest steps

---

## Phase 4 — Supply Chain & Developer Standards
**Milestones: M12**
⬜ Not started

Goals:
- Look production-safe to a security reviewer
- Demonstrate supply-chain security awareness
- Complete documentation promises from earlier phases

Key items:
- `composer audit` in CI
- CycloneDX SBOM (`bom.xml`) generated and committed
- `.editorconfig`, `SECURITY.md`, `CHANGELOG.md`
- AI safety document (`docs/ai-safety.md`)
- OWASP threat model sketch (`docs/threat-model.md`)
- PHPStan level bump (5 → 6)
- Test coverage report + CI badge

Why now: These are quick wins that dramatically improve the professional signal of the repository
before adding more features.

---

## Phase 5 — Public REST API
**Milestones: M13**
⬜ Not started

Goals:
- Make LedgerFlow accessible to programmatic clients
- Demonstrate API design discipline (versioning, resources, OpenAPI)
- Add Sanctum token auth as a complement to session auth

Key items:
- Laravel Sanctum personal access tokens
- `/api/v1/` with AccountResource, TransactionResource, ReconciliationRunResource
- JSON:API-style pagination + filtering
- OpenAPI 3.1 spec (`docs/openapi.yaml`)
- Scalar or Swagger UI embedded
- API Contract tests + rate limiting
- ADR-005: API auth strategy

Why now: The public API transforms LedgerFlow from "a nice demo" to "something you could build a
client on top of". It's also a prerequisite for future MCP write tools and external integrations.

---

## Phase 6 — Advanced AI (Embeddings, RAG, Conversational)
**Milestones: M14–M15**
⬜ Not started

Goals:
- Demonstrate the full breadth of the Laravel AI SDK
- Show practical AI-in-fintech patterns beyond simple explain/categorize

Key items:
- Semantic transaction search (Embeddings + cosine similarity)
- Conversational reconciliation assistant (RemembersConversations)
- Document Q&A / RAG (upload bank statement → FileSearch → answer questions)
- Auto-import from uploaded documents (structured output → RecordTransaction)
- Cashflow projection agent (HasStructuredOutput, series data)
- AI governance dashboard (per-agent costs from audit_events)
- Streaming explanations (->stream() in browser)
- Provider failover (OpenAI → Anthropic)
- Agent with MCP tool calling (AI calls get-account-summary internally)
- ADR-006: Embedding strategy, ADR-007: RAG pipeline

Why now: This is where LedgerFlow becomes distinctly "AI-native" rather than "AI-assisted". Each
feature maps to a specific Laravel AI SDK capability that isn't commonly demonstrated.

---

## Phase 7 — Observability & Deployment
**Milestones: M16**
⬜ Not started

Goals:
- Complete the production-platform story
- Demonstrate understanding of modern observability tooling
- Make the app deployable with a single command

Key items:
- OpenTelemetry traces (open-telemetry/opentelemetry-auto-laravel)
- Grafana / Loki / Tempo local stack (compose.observability.yaml)
- Laravel Cloud deployment (laravel.yml + deployment guide)
- Kubernetes / Helm design notes
- Operational runbooks (deploy, rollback, queue drain, DB backup)
- ADR-008: Observability stack selection

---

## Phase 8 — Fintech Domain Depth
**Milestones: M17**
⬜ Not started

Goals:
- Demonstrate accounting domain knowledge beyond basic CRUD
- Add features that require real domain modeling decisions

Key items:
- Transaction reversals (correction entries, reversal_of_id)
- Multi-currency accounts + FX rate model
- Account periods (period closing, anti-backdating)
- Approval workflows (two-person rule for high-value entries)
- Holds / reservations (reserve before clear)
- Merchant normalization (AI maps raw → canonical)
- ADR-009: Single-entry vs double-entry bookkeeping

Why last: These are incremental domain features. The platform story (API, AI depth, observability)
provides more differentiation than transaction reversals for a portfolio context.

---

## Prioritized Backlog Summary

### 🔴 High — Start here

1. `composer audit` in CI (30 min)
2. `.editorconfig` (10 min)
3. `SECURITY.md` (30 min)
4. AI safety document `docs/ai-safety.md` (1 hr)
5. `CHANGELOG.md` with git-cliff (30 min)
6. CycloneDX SBOM in CI (1 hr)
7. PHPStan level 6 (assess + fix)
8. Test coverage badge in CI

### 🟡 Medium — Phase 5

9.  Laravel Sanctum + `/api/v1/` routes
10. AccountResource, TransactionResource, ReconciliationRunResource
11. OpenAPI spec (`docs/openapi.yaml`)
12. API Contract + Feature tests

### 🟢 High-value AI — Phase 6

13. Semantic transaction search (Embeddings)
14. Conversational reconciliation assistant (RemembersConversations)
15. AI governance dashboard (from audit_events)
16. Cashflow projection agent
17. Streaming AI responses
18. Document Q&A / RAG

### 🔵 Platform — Phase 7

19. OpenTelemetry traces
20. Laravel Cloud deployment config
21. Observability notes doc (Grafana/Loki/Tempo)
22. Runbooks

### ⚪ Domain depth — Phase 8

23. Transaction reversals
24. Account periods
25. Multi-currency
26. Approval workflows

---

## Done Criteria per Phase

| Phase | Done when… |
|-------|-----------|
| 4 (Standards) | `composer audit` clean, SBOM committed, AI safety doc written, PHPStan ≥ 6 |
| 5 (API) | `/api/v1/` returns resources, OpenAPI spec validates, Contract tests pass |
| 6 (AI depth) | Embeddings search works, conversational assistant has memory, RAG returns answers from uploaded PDF |
| 7 (Ops) | OTel traces visible in local Tempo, deployment guide tested end-to-end |
| 8 (Domain) | Transaction reversals create correct counter-entries, period close blocks backdating |
