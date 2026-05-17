# LedgerFlow — Ecosystem Graph

A complete map of every tool, library, standard, and service relevant to this project.

Legend:
- ✅ **In use** — installed and actively used
- 🔧 **Configured** — present but not fully exploited
- 📋 **Planned** — in the roadmap (see `milestones.md`)
- 💡 **Candidate** — worth evaluating; not yet committed
- ❌ **Rejected** — considered and ruled out (with reason)

---

## Language & Runtime

| Tool | Status | Notes |
|------|--------|-------|
| PHP 8.4 | ✅ | Required in `composer.json` |
| Node.js 22 | ✅ | Frontend toolchain, CI |
| Composer v2 | ✅ | PHP package manager |
| npm | ✅ | JS package manager |

---

## Framework & Core

| Tool | Status | Notes |
|------|--------|-------|
| Laravel 13 | ✅ | `laravel/framework ^13.7` |
| Laravel Fortify | ✅ | Auth backend (login, register, 2FA ready) |
| Laravel Tinker | ✅ | REPL for local debugging |
| Laravel Sail | ✅ | Docker dev environment |
| Laravel Pail | ✅ | Tail logs in dev |
| Laravel Pao | 🔧 | Installed (via Boost), not directly used |
| Laravel Boost | ✅ | AI coding agent guidelines + skills |

---

## Frontend

| Tool | Status | Notes |
|------|--------|-------|
| Livewire 4 | ✅ | Reactive server-side UI |
| Flux UI (livewire/flux) | ✅ | Component library (buttons, modals, tables, forms) |
| Tailwind CSS v4 | ✅ | Utility CSS |
| Vite 8 | ✅ | Asset bundler |
| Alpine.js | 💡 | Included transitively by Livewire; could use for client-side interactivity |
| Inertia.js | ❌ | Rejected — Livewire preferred for server-side domain logic |
| React / Vue | ❌ | Rejected — no SPA needed for this scope |

---

## Database

| Tool | Status | Notes |
|------|--------|-------|
| PostgreSQL 17 | ✅ | Only supported DB (ADR-001) |
| Redis 7 | ✅ | Cache, queues, sessions |
| Eloquent ORM | ✅ | Query builder + models |
| Laravel Migrations | ✅ | Schema management |
| Laravel Factories | ✅ | Test data generation |
| pgBouncer | 💡 | Connection pooling for production |
| SQLite | ❌ | Rejected — lacks ILIKE, advisory locks (ADR-001) |
| MySQL | ❌ | Rejected — team preference PostgreSQL (ADR-001) |

---

## Authentication & Authorization

| Tool | Status | Notes |
|------|--------|-------|
| Laravel Fortify | ✅ | Auth backend |
| Laravel Policies | ✅ | AccountPolicy, TransactionPolicy |
| Role enum (Owner/Member) | ✅ | Org-scoped RBAC |
| Laravel Sanctum | 📋 | Planned for M13 — API token auth |
| Laravel Passport | ❌ | Rejected — OAuth2 overkill for current scope |
| Laravel Socialite | 💡 | Social login (Google/GitHub); easy add-on |
| Two-factor auth (TOTP) | 💡 | Fortify has 2FA support; not enabled |
| Passkeys (WebAuthn) | 💡 | Fortify supports passkeys in v1.x |

---

## Queues & Jobs

| Tool | Status | Notes |
|------|--------|-------|
| Laravel Horizon | ✅ | Queue dashboard + worker supervision |
| Redis queue driver | ✅ | Primary queue backend |
| `ProcessWebhookEvent` job | ✅ | Idempotent webhook processing |
| `ReconcileOrganization` job | ✅ | Async reconciliation |
| Laravel Schedule | 💡 | Artisan scheduler — no scheduled jobs yet |
| Outbox pattern | 💡 | Replace direct dispatch with DB outbox for guaranteed delivery |
| Laravel Pennant | 💡 | Feature flags for gradual job rollout |

---

## AI & Machine Learning

| Tool | Status | Notes |
|------|--------|-------|
| Laravel AI SDK (`laravel/ai`) | ✅ | Official first-party AI SDK (ADR-003) |
| `Promptable` trait | ✅ | Used by all 4 agents |
| `HasStructuredOutput` | ✅ | TransactionCategorizer returns typed array |
| `#[UseCheapestModel]` | ✅ | Cost-optimised agent routing |
| Prism PHP (`prism-php/prism`) | 🔧 | Installed but not directly used (superseded by laravel/ai) |
| `RemembersConversations` | 📋 | M14 — conversational reconciliation assistant |
| `Embeddings::for()` | 📋 | M14 — semantic transaction search |
| `Reranking::of()` | 📋 | M14 — re-rank reconciliation candidates |
| `Stores` (vector stores) | 📋 | M15 — RAG document pipeline |
| `Document::fromPath()` | 📋 | M15 — upload bank statement to provider |
| `FileSearch` provider tool | 📋 | M15 — AI Q&A over uploaded documents |
| `WebSearch` provider tool | 💡 | AI looks up merchant name online |
| `->stream()` | 📋 | M14 — real-time streaming UI |
| Broadcasting (AI result) | 📋 | M14 — push queued AI result to browser |
| Provider failover | 📋 | M14 — `[Lab::OpenAI, Lab::Anthropic]` |
| Agent with MCP tools | 📋 | M14 — `HasTools` calling internal MCP tools |
| `Audio` (TTS) | 💡 | Read out financial summaries |
| `Transcription` (STT) | 💡 | Voice-driven transaction entry |
| OpenAI | ✅ | Default provider (via `OPENAI_API_KEY`) |
| Anthropic Claude | 💡 | Failover or `#[UseSmartestModel]` |
| Gemini | 💡 | Cost alternative for embeddings |
| Ollama (local) | 💡 | Air-gapped / no-key local dev mode |

---

## MCP (Model Context Protocol)

| Tool | Status | Notes |
|------|--------|-------|
| `laravel/mcp` | ✅ | MCP server framework |
| `LedgerFlowServer` | ✅ | 4 read-only org-scoped tools |
| `get-account-summary` | ✅ | Account list with balances |
| `search-transactions` | ✅ | Filter by description/type/date |
| `list-reconciliation-issues` | ✅ | Status-filtered issue list |
| `list-audit-events` | ✅ | Event-name-filtered audit log |
| MCP auth (session) | ✅ | `auth` middleware on `/mcp/ledgerflow` |
| MCP token auth | 📋 | M13 — personal access token instead of session cookie |
| MCP write tools | 💡 | `record-transaction` with owner-only guard |
| MCP usage audit | 📋 | Log every tool call to `audit_events` |

---

## Webhooks & Integrations

| Tool | Status | Notes |
|------|--------|-------|
| `WebhookController` | ✅ | Idempotent `POST /webhooks/{provider}` |
| Stripe HMAC-SHA256 | ✅ | Signature validation (`config/webhooks.php`) |
| Mollie signature | 💡 | Stub — needs real implementation |
| Provider sandbox | 💡 | Local Stripe CLI for test events |
| Webhook replay | ✅ | Owner-only re-queue for failed events |

---

## Operations & Observability

| Tool | Status | Notes |
|------|--------|-------|
| Laravel Horizon | ✅ | Queue dashboard at `/horizon` |
| Laravel Pulse | ✅ | Performance monitoring at `/pulse` |
| Laravel Telescope | ✅ | Request/query/job inspector at `/telescope` (non-prod) |
| `/health` endpoint | ✅ | Public JSON — DB + cache + queue |
| Structured JSON logging | ✅ | `json` channel (`LOG_CHANNEL=json`) |
| OpenTelemetry | 📋 | M16 — `open-telemetry/opentelemetry-auto-laravel` |
| Grafana | 📋 | M16 — dashboard for metrics (local Docker Compose) |
| Loki | 📋 | M16 — log aggregation |
| Tempo | 📋 | M16 — distributed tracing |
| Prometheus | 💡 | Metrics scraping (alternative to Loki/Grafana stack) |
| Sentry | 💡 | Error tracking + performance (SaaS) |
| Datadog | 💡 | Full observability SaaS — heavier |
| Laravel Cloud | 📋 | M16 — `laravel.yml` deployment config |

---

## Static Analysis & Code Quality

| Tool | Status | Notes |
|------|--------|-------|
| PHPStan / Larastan | ✅ | Level 5, `phpstan.neon`, 0 errors |
| Laravel Pint | ✅ | Opinionated code style fixer (PSR-12 + Laravel) |
| **Psalm** | 💡 | Alternative/complementary to PHPStan; strong type inference, taint analysis for security |
| **PHP_CodeSniffer (PHPCS)** | 💡 | PSR-12 + custom sniffs; more granular than Pint for team style enforcement |
| **PHPMD (PHP Mess Detector)** | 💡 | Detects dead code, overcomplicated methods, high cyclomatic complexity |
| **Rector** | 📋 | Automated PHP upgrades + refactoring; excellent for PHP 8.x → 8.y migrations and code modernization |
| PHPStan level raise | 📋 | M12 — bump from 5 to 6 or 7 |
| Taint analysis (Psalm) | 💡 | Psalm's `--taint-analysis` flag — detects SQL injection, XSS at the code level |

### Comparison: PHPStan vs Psalm vs PHPMD vs PHPCS

| Tool | Best for |
|------|---------|
| **PHPStan / Larastan** | Type correctness, undefined methods/properties, Laravel-specific rules |
| **Psalm** | Deeper type inference, security taint analysis, immutability checking |
| **PHPMD** | Code quality metrics (complexity, duplication, dead code) |
| **PHP_CodeSniffer** | Enforcing a specific style ruleset beyond what Pint covers (e.g. doc-comment rules) |
| **Rector** | Automated code modernisation — upgrade PHP syntax, remove deprecated patterns |

**Recommendation**: PHPStan (already in use) + Rector (automated upgrades) is the highest-value pair.
PHPMD and PHPCS add noise unless you have a large team. Psalm's taint analysis is worth adding for
fintech security context.

---

## Testing

| Tool | Status | Notes |
|------|--------|-------|
| Pest 4 | ✅ | Primary test runner |
| pest-plugin-laravel | ✅ | Laravel helpers (`actingAs`, `RefreshDatabase`, etc.) |
| PHPUnit 12 | ✅ | Underlying test engine |
| Mockery | ✅ | Mock objects |
| Faker | ✅ | Test data generation |
| Playwright | ✅ | E2E browser tests (102 tests) |
| k6 | ✅ | Performance / load tests (tests/performance/load.js) |
| Gherkin (.feature files) | ✅ | Behavioral specs (tests/behavioral/) |
| Behat | 💡 | Execute Gherkin .feature files as tests (currently files are spec-only) |
| Infection PHP | 💡 | Mutation testing — measures how good your tests really are |
| Codecov / Coveralls | 📋 | M12 — coverage reporting SaaS with PR badge |
| Pest `--coverage` | 📋 | M12 — Clover XML coverage in CI |
| Laravel Dusk | 💡 | Alternative to Playwright for browser tests (PHP-native) |
| Contract tests | ✅ | `tests/Contract/` — AccountContractTest, etc. |
| Smoke tests | ✅ | `tests/Smoke/ApplicationSmokeTest.php` |

---

## CI/CD

| Tool | Status | Notes |
|------|--------|-------|
| GitHub Actions | ✅ | `.github/workflows/tests.yml` |
| shivammathur/setup-php | ✅ | PHP 8.4 in CI |
| PostgreSQL 17 service | ✅ | CI service container |
| Redis 7 service | ✅ | CI service container |
| `composer audit` | 📋 | M12 — dependency vulnerability check |
| Dependabot | 💡 | Auto-PR for dependency updates |
| Renovate | ✅ | `renovate.json` present — auto-dependency updates |
| Preview environments | 💡 | PR-based preview deploys (e.g. Laravel Cloud) |
| Release automation | 📋 | See Release & Versioning section |

---

## Release & Versioning

| Tool | Status | Notes |
|------|--------|-------|
| Conventional Commits | ✅ | Commit message standard (enforced by convention) |
| Semantic Versioning | ✅ | Convention adopted; no git tags yet |
| **`.releaserc`** | 📋 | `semantic-release` config — automates version bump + CHANGELOG + GitHub Release from CI. Uses conventional commits to determine `patch`/`minor`/`major`. Works with `@semantic-release/changelog`, `@semantic-release/git`, `@semantic-release/github`. |
| **`git-cliff`** | 📋 | M12 — Rust-based CHANGELOG generator from conventional commits; simpler than semantic-release, no version bumping |
| `commitlint` | 💡 | Lint commit messages against Conventional Commits spec (pre-commit hook or CI) |
| `husky` | 💡 | Git hook manager for commitlint + pre-commit Pint |
| GitHub Releases | 📋 | M12 — create tagged releases |
| `CHANGELOG.md` | 📋 | M12 — generated by git-cliff or semantic-release |

### semantic-release vs git-cliff

| | `semantic-release` | `git-cliff` |
|--|--|--|
| Language | Node.js | Rust |
| What it does | Version bump + CHANGELOG + Git tag + GitHub Release, all automated from CI | CHANGELOG generation only |
| Config | `.releaserc` (JSON/YAML) | `cliff.toml` |
| Best for | Full CD automation — "push to main → release created" | Simpler changelog generation, no release automation |
| **Recommendation** | Use if you want fully automated releases | Use for a manual-but-formatted changelog |

**Recommendation**: Add `git-cliff` first (simple, standalone). Add `semantic-release` + `.releaserc`
when you want CI to automatically create versioned GitHub Releases.

---

## API Design & Documentation

| Tool | Status | Notes |
|------|--------|-------|
| No public API yet | — | Web + MCP only |
| **OpenAPI 3.1 spec** | 📋 | M13 — `docs/openapi.yaml`; hand-authored or generated |
| **dedoc/scramble** | 📋 | M13 — auto-generate OpenAPI spec from Laravel routes + docblocks |
| **Swagger UI** | 📋 | M13 — embedded spec browser |
| **Scalar** | 📋 | M13 — modern alternative to Swagger UI (cleaner, better DX) |
| JSON:API spec | 📋 | M13 — response envelope format |
| Laravel Sanctum | 📋 | M13 — API token auth |
| Laravel API Resources | 📋 | M13 — AccountResource, TransactionResource, etc. |

---

## Supply Chain & Security Standards

| Tool | Status | Notes |
|------|--------|-------|
| `SECURITY.md` | 📋 | M12 — responsible disclosure policy |
| **CycloneDX SBOM** | 📋 | M12 — `cyclonedx/cyclonedx-php-composer` generates `bom.xml`; machine-readable Software Bill of Materials. Required in regulated fintech environments. |
| `composer audit` | 📋 | M12 — checks installed packages against PHP Security Advisories Database |
| `npm audit` | 💡 | Check JS dependencies for CVEs |
| Dependabot security alerts | 💡 | GitHub-native CVE alerts (free) |
| Snyk | 💡 | Deeper SCA + container scanning SaaS |
| OWASP ZAP | 💡 | DAST — scan running app for OWASP Top 10 vulnerabilities |
| **Psalm taint analysis** | 💡 | Static taint tracking for SQL injection, XSS at code level |
| SOPS | 💡 | Secrets encryption for `.env` files in git |
| HashiCorp Vault | 💡 | Secrets management for production |
| Doppler | 💡 | SaaS secrets manager — simpler than Vault |

---

## Deployment & Infrastructure

| Tool | Status | Notes |
|------|--------|-------|
| Docker / Docker Compose | ✅ | `compose.yaml` for local dev via Sail |
| Laravel Cloud | 📋 | M16 — `laravel.yml` + deployment guide |
| Kubernetes | 💡 | Design notes only (M16); no running cluster needed for portfolio |
| Helm | 💡 | K8s packaging; document alongside K8s notes |
| **Rector** | 📋 | M12 (dev tooling) — also useful pre-deployment for ensuring no deprecated patterns |
| Multi-stage Dockerfile | 💡 | Production-optimised image (separate build + runtime stages) |
| GitHub Container Registry | 💡 | Store Docker images for deployment |
| Fly.io | 💡 | Simple alternative to Laravel Cloud |

---

## Developer Experience

| Tool | Status | Notes |
|------|--------|-------|
| `.editorconfig` | 📋 | M12 — cross-editor whitespace/indent |
| `husky` | 💡 | Git hook manager |
| `commitlint` | 💡 | Lint conventional commit messages |
| **Rector** | 📋 | Automated PHP refactoring + upgrade; `vendor/bin/rector` |
| VS Code devcontainer | 💡 | `devcontainer.json` for one-click dev environment |
| Laravel Herd | 💡 | Alternative to Sail for macOS-native dev |
| PHP CS Fixer | 💡 | Alternative to Pint (Pint wraps it; no need to add directly) |
| Makefile | 💡 | Top-level `make dev`, `make test`, `make lint` shortcuts |

---

## Documentation

| Tool | Status | Notes |
|------|--------|-------|
| Markdown docs | ✅ | `docs/`, `planning/`, `README.md` |
| Architecture doc | ✅ | `docs/architecture.md` |
| AI strategy | ✅ | `docs/ai-strategy.md` |
| Setup guide | ✅ | `docs/setup.md` |
| Demo script | ✅ | `docs/demo-script.md` |
| ADR-001–004 | ✅ | `docs/adr/` |
| AI safety doc | 📋 | M12 — `docs/ai-safety.md` |
| Threat model | 📋 | M12 — `docs/threat-model.md` |
| OpenAPI spec | 📋 | M13 — `docs/openapi.yaml` |
| Observability guide | 📋 | M16 — `docs/observability.md` |
| Deployment guide | 📋 | M16 — `docs/deployment.md` |
| Runbooks | 📋 | M16 — `docs/runbooks/` |
| `CHANGELOG.md` | 📋 | M12 — conventional commits → changelog |
| `SECURITY.md` | 📋 | M12 — responsible disclosure |
| Interview talking points | 💡 | Per-milestone "what I built and why" notes |
| Architecture diagrams | 💡 | C4 model or Mermaid flowcharts in docs/ |

---

## Summary: Highest-Value Additions

Ordered by impact vs effort, for tools not yet in use:

| Priority | Tool | Why |
|----------|------|-----|
| 🔴 1 | `composer audit` in CI | Free, 1-line addition, fintech security signal |
| 🔴 2 | `git-cliff` + `CHANGELOG.md` | Low effort, high professionalism signal |
| 🔴 3 | CycloneDX SBOM | Supply-chain standard; required in regulated contexts |
| 🔴 4 | `.releaserc` / semantic-release | Automated GitHub Releases from CI |
| 🟡 5 | **Rector** | Automates PHP upgrades; impressive tooling awareness |
| 🟡 6 | OpenAPI spec + Scalar UI | Required for M13 API; makes LedgerFlow look production-grade |
| 🟡 7 | Laravel Sanctum | Unlocks the public API milestone |
| 🟡 8 | **Psalm taint analysis** | Security-focused static analysis; notable in fintech context |
| 🟢 9 | Embeddings (semantic search) | Impressive AI feature; single `Embeddings::for()` call |
| 🟢 10 | semantic-release (`.releaserc`) | Full CD automation once releases are versioned |
| 🔵 11 | OpenTelemetry | Distributed traces; completes the observability story |
| 🔵 12 | Behat | Execute the .feature specs as real tests |
| 🔵 13 | Infection PHP | Mutation testing; demonstrates test quality awareness |
| ⚪ 14 | PHPMD | Cyclomatic complexity checks; lower priority with existing PHPStan |
| ⚪ 15 | PHPCS | Fine-grained style rules; Pint already covers most of this |
