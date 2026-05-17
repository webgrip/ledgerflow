# Priority List

Quick-reference of what to build next, in order.
See `roadmap.md` for full rationale and `milestones.md` for deliverables per milestone.

---

## ✅ Completed (Phases 1–3)

Phases 1 through 3 are done. See `milestones.md` M0–M11.

Summary of what exists:
- Auth + org workspace + RBAC
- Accounts + transactions + filters + CSV import/export
- Webhook ingestion (idempotent, HMAC validated, replay)
- Reconciliation + AI analysis
- AI agents: explain, categorize, summarize, analyse
- MCP server (4 read-only tools)
- Horizon + Pulse + Telescope + `/health`
- Rate limits + tenant isolation + audit log
- Test pyramid (Unit/Integration/Functional/Contract/Smoke/E2E/Behavioral/Performance)
- CI (Pint + PHPStan + Pest + Postgres + Redis)
- README + 4 ADRs + docs

---

## 🔴 Phase 4 — Supply Chain & Standards (M12)

Quick wins. All items are < 1 day of effort each.

| # | Item | Notes |
|---|------|-------|
| 1 | `composer audit` in CI | Add step to `.github/workflows/tests.yml` |
| 2 | `.editorconfig` | Whitespace/indent for all editors |
| 3 | `SECURITY.md` | Responsible disclosure template |
| 4 | AI safety doc (`docs/ai-safety.md`) | Advisory-only principles, prompt scoping, audit trail |
| 5 | `CHANGELOG.md` | `git-cliff` config + initial log |
| 6 | CycloneDX SBOM | `cyclonedx-php-composer` + CI step → `bom.xml` |
| 7 | PHPStan level 6 | Assess new errors, fix or suppress with justification |
| 8 | Coverage badge | `--coverage` in CI + badge in README |
| 9 | OWASP threat model sketch (`docs/threat-model.md`) | Identify key attack surfaces |

---

## 🟡 Phase 5 — Public REST API (M13)

Turns LedgerFlow into something clients can integrate with.

| # | Item | Notes |
|---|------|-------|
| 10 | Laravel Sanctum | `composer require laravel/sanctum` + token API |
| 11 | `/api/v1/` routes | Versioned, separate from web routes |
| 12 | API Resources | Account, Transaction, ReconciliationRun, Organization |
| 13 | API policies | Reuse existing policies, add token-scope check |
| 14 | OpenAPI spec | `docs/openapi.yaml` — hand-authored or via `dedoc/scramble` |
| 15 | API tests | Feature tests + Contract tests per resource |
| 16 | ADR-005 | API authentication strategy decision |

---

## 🟢 Phase 6 — AI Depth (M14–M15)

Demonstrates the full Laravel AI SDK surface area.

| # | Item | Laravel AI SDK feature |
|---|------|----------------------|
| 17 | Semantic transaction search | `Embeddings::for()->generate()` + similarity query |
| 18 | AI governance dashboard | Query `audit_events` where event=`ai.agent_called` |
| 19 | Conversational reconciliation assistant | `RemembersConversations` trait |
| 20 | Streaming explanations | `->stream()` → SSE response |
| 21 | Cashflow projection agent | `HasStructuredOutput` returning series data |
| 22 | Provider failover | `prompt(..., provider: [Lab::OpenAI, Lab::Anthropic])` |
| 23 | Agent with MCP tool calling | `HasTools` + `get-account-summary` as internal tool |
| 24 | Document Q&A (RAG) | `Document::fromPath()->put()` + `Stores` + `FileSearch` |
| 25 | ADR-006 | Embedding strategy |
| 26 | ADR-007 | RAG document pipeline |

---

## 🔵 Phase 7 — Observability & Deployment (M16)

Completes the production-platform story.

| # | Item | Notes |
|---|------|-------|
| 27 | OpenTelemetry | `open-telemetry/opentelemetry-auto-laravel`, OTLP traces |
| 28 | Observability notes | `docs/observability.md` — Grafana/Loki/Tempo |
| 29 | Laravel Cloud deploy | `laravel.yml` + `docs/deployment.md` |
| 30 | Runbooks | `docs/runbooks/` — deploy, rollback, queue drain |
| 31 | ADR-008 | Observability stack selection |

---

## ⚪ Phase 8 — Fintech Domain Depth (M17)

Incremental domain features. Pull in when the platform story is complete.

| # | Item | Notes |
|---|------|-------|
| 32 | Transaction reversals | `reversal_of_id` FK, counter-entry creation |
| 33 | Account periods | Period close, anti-backdating guard |
| 34 | Multi-currency | FX rate model, currency per transaction |
| 35 | Approval workflows | Owner approval gate on high-value entries |
| 36 | Merchant normalization | AI maps raw description → canonical merchant |
| 37 | ADR-009 | Single-entry vs double-entry decision |

---

## Deferred / Out of Scope (for now)

- Mollie webhook signature validation (stub is sufficient for demo)
- Kubernetes running cluster (design notes only)
- Live FX rates (model design in ADR is sufficient)
- OAuth social login (Socialite) — not a fintech requirement
- Mobile / React Native client — out of scope for portfolio focus
