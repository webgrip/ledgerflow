# Backlog

Items marked ✅ are shipped. Items marked ⬜ are available to pick up.
Use the roadmap and milestones to decide what to pull in next.

---

## Foundation

- ✅ Laravel starter kit
- ✅ auth (Fortify — login, register, email verify)
- ✅ organizations
- ✅ memberships
- ✅ roles (Owner / Member enum)
- ✅ policies (AccountPolicy, TransactionPolicy)
- ✅ profile / settings
- ✅ seed / demo data (e2e:seed command)
- ⬜ team invitations (invite by email)
- ⬜ org deletion / transfer ownership

## Accounts and Transactions

- ✅ accounts (Asset, Liability, Revenue, Expense)
- ✅ account types
- ✅ transaction records (credit/debit, minor units)
- ✅ balance display
- ✅ transaction filters (search, type, date range — URL-bound)
- ✅ transaction search
- ✅ CSV import (modal, validate, dedup)
- ✅ CSV export (streamed download)
- ⬜ transaction reversals (correction entries linked by reversal_of_id)
- ⬜ transaction approval workflows (owner must approve high-value entries)
- ⬜ account periods (close a period, block backdating)
- ⬜ holds / reservations (reserve funds before clearing)
- ⬜ multi-currency accounts + FX model
- ⬜ PDF bank statement import

## Payments and Webhooks

- ✅ provider abstraction (provider column, match/dispatch pattern)
- ✅ webhook event storage (provider_events table)
- ✅ Stripe HMAC-SHA256 signature validation
- ✅ idempotent processing (idempotency_key + job guard)
- ✅ webhook replay tool (owner-only, resets to Pending + re-dispatches)
- ✅ webhooks page (/webhooks, filterable)
- ⬜ Mollie signature validation (currently stub)
- ⬜ provider sandbox (test event generator)
- ⬜ failed webhook alerting (notify owner when job permanently fails)

## Reconciliation

- ✅ reconciliation runs
- ✅ reconciliation issues
- ✅ matching logic (RunReconciliation action)
- ✅ dashboard/list/show views
- ✅ AI explanation for mismatches (ReconciliationAnalyst)
- ⬜ manual issue resolve / dismiss flow (currently status enum but no UI button)
- ⬜ automatic match suggestions (AI proposes which transaction matches which issue)
- ⬜ reconciliation reports (PDF or CSV summary per run)
- ⬜ scheduled auto-reconciliation (daily Artisan command)

## AI

- ✅ transaction explanation (TransactionExplainer)
- ✅ transaction categorization (TransactionCategorizer — structured output)
- ✅ account activity summary (AccountActivitySummarizer)
- ✅ reconciliation issue analysis (ReconciliationAnalyst)
- ✅ AI call audit log (AuditLogger::logAiCall — agent, model, token counts)
- ⬜ AI governance dashboard (per-agent call counts, token totals, estimated cost)
- ⬜ semantic transaction search (Embeddings::for() + cosine similarity)
- ⬜ conversational reconciliation assistant (RemembersConversations, chat per run)
- ⬜ cashflow projection agent (30/60/90-day forecast, HasStructuredOutput)
- ⬜ merchant normalization (raw description → canonical merchant name)
- ⬜ support response draft assistant
- ⬜ document / statement Q&A (RAG via FileSearch provider tool)
- ⬜ AI fraud / anomaly triage assistant
- ⬜ AI streaming responses (->stream() for real-time browser output)
- ⬜ AI broadcasting (push queued AI result to browser)
- ⬜ provider failover (prompt(..., provider: [Lab::OpenAI, Lab::Anthropic]))
- ⬜ agent with MCP tool calling (AI calls get-account-summary internally)

## MCP

- ✅ read-only account summary (get-account-summary)
- ✅ read-only transaction search (search-transactions)
- ✅ read-only reconciliation issue list (list-reconciliation-issues)
- ✅ read-only audit event list (list-audit-events)
- ✅ MCP authorization (auth middleware)
- ✅ MCP tests (13 tests covering tools + auth)
- ⬜ MCP usage audit (log every MCP tool call to audit_events)
- ⬜ write-capable MCP tools (record-transaction — with explicit owner-only guard)
- ⬜ MCP token auth (personal access token instead of session cookie)

## Public API

- ⬜ Laravel Sanctum API token auth
- ⬜ versioned routes: /api/v1/
- ⬜ OrganizationResource, AccountResource, TransactionResource, ReconciliationRunResource
- ⬜ JSON:API-style pagination + filtering
- ⬜ OpenAPI specification (docs/openapi.yaml)
- ⬜ API docs (Scalar or Swagger UI)
- ⬜ API rate limiting
- ⬜ API tests (Feature + Contract)

## Operations

- ✅ queues (Redis + Laravel Horizon)
- ✅ Horizon (queue dashboard)
- ✅ Pulse (performance monitoring)
- ✅ Telescope (local dev request/query inspector)
- ✅ structured logs (json channel)
- ✅ health checks (GET /health — DB + cache + queue)
- ⬜ OpenTelemetry (traces for HTTP + queue jobs via open-telemetry/opentelemetry-auto-laravel)
- ⬜ Grafana / Loki / Tempo notes (docs/observability.md + optional local Docker Compose stack)
- ⬜ Laravel Cloud deployment (laravel.yml + docs/deployment.md)
- ⬜ Kubernetes / Helm design notes (docs/kubernetes.md)
- ⬜ operational runbooks (docs/runbooks/)
- ⬜ scheduled reconciliation Artisan command

## Security

- ✅ rate limits (named limiters: api, webhooks, ai)
- ✅ tenant-isolation tests (cross-org 403 for all resources)
- ✅ audit coverage (all mutations + AI calls logged)
- ✅ export controls (view policy enforced on CSV export)
- ✅ webhook HMAC signature validation
- ⬜ composer audit in CI (dependency vulnerability scanning)
- ⬜ CycloneDX SBOM (bom.xml generated in CI)
- ⬜ SECURITY.md (responsible disclosure policy)
- ⬜ OWASP threat model (docs/threat-model.md)
- ⬜ prompt redaction (strip PII from AI prompt metadata in audit log)
- ⬜ row-level security (PostgreSQL RLS on org-scoped tables — ADR first)
- ⬜ PHPStan level 6–7 (raise from current level 5)
- ⬜ test coverage report in CI (--coverage, Clover XML, badge)

## Standardization

- ⬜ `.editorconfig` (cross-editor whitespace/indent standard)
- ⬜ `CHANGELOG.md` — use **git-cliff** (`git-cliff/git-cliff`) for conventional commits → changelog
- ⬜ `SECURITY.md` (responsible disclosure)
- ⬜ `commitlint` (`@commitlint/config-conventional`) — lint commit messages in CI or via husky pre-commit-msg hook
- ⬜ `husky` — git hook manager for commitlint + pre-commit Pint
- ⬜ CycloneDX SBOM (`cyclonedx/cyclonedx-php-composer`) — `bom.json` generated and committed
- ⬜ OpenAPI 3.1 spec (required for M13 Public API — use `dedoc/scramble` for auto-generation)
- ⬜ **semantic-release** (`.releaserc.json`) — fully automated version bump + CHANGELOG + GitHub Release from CI; `@semantic-release/changelog`, `@semantic-release/git`, `@semantic-release/github`
- ⬜ semantic versioning releases (GitHub Releases + git tags — prerequisite for semantic-release)

## Code Quality (Candidates — evaluate per milestone)

- ⬜ **Rector** (`rector/rector`) — automated PHP syntax modernisation + refactoring; run `--dry-run` first; excellent before PHP version bumps; config: `rector.php`
- ⬜ **Psalm** (`vimeo/psalm` + `psalm/plugin-laravel`) — stricter type inference than PHPStan; adds taint analysis for SQL injection / XSS detection (valuable in fintech context)
- ⬜ **PHPMD** (`phpmd/phpmd`) — cyclomatic complexity + dead code + God-class detection; run in warn-only mode (noisy); lower priority given PHPStan already active
- ⬜ **PHP_CodeSniffer** (`squizlabs/php_codesniffer`) — PSR-12 + custom sniff enforcement; largely superseded by Pint for this project; add only for team-specific style rules Pint can't express
- ⬜ **PHPInsights** (`nunomaduro/phpinsights`) — holistic quality score (style + complexity + architecture + security); good for portfolio "health score" display
- ⬜ **Infection PHP** (`infection/infection`) — mutation testing; measures how much of your code is *meaningfully* tested (vs just covered)

## Documentation

- ✅ README (tech stack, quick start, demo credentials, MCP docs, feature table)
- ✅ setup guide (docs/setup.md)
- ✅ roadmap (planning/roadmap.md)
- ✅ ADR-001 PostgreSQL
- ✅ ADR-002 Livewire + Flux
- ✅ ADR-003 Laravel AI SDK
- ✅ ADR-004 Idempotent webhooks
- ✅ architecture overview (docs/architecture.md)
- ✅ AI strategy (docs/ai-strategy.md)
- ✅ demo script (docs/demo-script.md)
- ⬜ AI safety document (docs/ai-safety.md) — advisory-only principles, prompt scoping, audit requirements
- ⬜ ADR-005 API authentication strategy
- ⬜ ADR-006 Embedding and vector store strategy
- ⬜ ADR-007 RAG document pipeline
- ⬜ ADR-008 Observability stack
- ⬜ ADR-009 Double-entry vs single-entry accounting model
- ⬜ threat model (docs/threat-model.md)
- ⬜ observability guide (docs/observability.md)
- ⬜ deployment guide (docs/deployment.md)
- ⬜ Kubernetes / Helm notes (docs/kubernetes.md)
- ⬜ API docs
- ⬜ MCP connection guide (how to connect Cursor/Claude Desktop)
- ⬜ interview talking points
