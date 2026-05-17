# LedgerFlow

> A portfolio-grade Laravel fintech platform demonstrating modern PHP engineering patterns: multi-tenant workspaces, domain actions, AI-powered ledger analysis, idempotent webhook ingestion, reconciliation, and a read-only MCP server for AI clients.

[![Tests](https://github.com/webgrip/ledgerflow/actions/workflows/tests.yml/badge.svg)](https://github.com/webgrip/ledgerflow/actions/workflows/tests.yml)
[![PHP](https://img.shields.io/badge/PHP-8.4-7A86B8.svg)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20.svg)](https://laravel.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

---

## Table of Contents

1. [Overview](#overview)
2. [Feature Matrix](#feature-matrix)
3. [Tech Stack](#tech-stack)
4. [Architecture at a Glance](#architecture-at-a-glance)
5. [Quick Start (new dev)](#quick-start-new-dev)
6. [Environment Variables](#environment-variables)
7. [Seeded Demo Data](#seeded-demo-data)
8. [Available Routes](#available-routes)
9. [Developer Tools](#developer-tools)
10. [Running Tests](#running-tests)
11. [Code Quality](#code-quality)
12. [Project Structure](#project-structure)
13. [Architecture Decisions](#architecture-decisions)
14. [AI Features](#ai-features)
15. [MCP Integration](#mcp-integration)
16. [Webhook Integration](#webhook-integration)
17. [Contributing](#contributing)
18. [Roadmap](#roadmap)
19. [License](#license)

---

## Overview

LedgerFlow is a multi-tenant financial ledger built to showcase how a production-quality Laravel
application is structured, tested, and operated. It is intentionally feature-rich so that every
layer of the stack is exercised — from Eloquent policies and queued jobs to streaming AI responses
and a Model Context Protocol server.

**What makes it interesting to engineers:**

- Domain actions encapsulate every state-changing operation — controllers and Livewire components
  stay thin.
- Every mutation is written to an immutable audit log — nothing is lost.
- Webhook events are deduplicated by provider event ID and processed asynchronously — exactly-once
  semantics at the application level.
- AI agents are first-class citizens; usage is audited, token counts are recorded, and each agent
  is independently testable via `Agent::fake()`.
- The MCP server exposes four read-only tools scoped to the authenticated user's organization,
  compatible with Cursor, VS Code Copilot Chat, and Claude Desktop out of the box.

---

## Feature Matrix

| Area | Feature | Details |
|------|---------|---------|
| **Auth** | Registration & login | Laravel Fortify; email verification ready |
| **Auth** | Session security | Fortify password confirmation, CSRF |
| **Multi-tenancy** | Organizations | Each user belongs to one or more orgs |
| **Multi-tenancy** | Role-based access | Owner / Member scope; policies on every model |
| **Multi-tenancy** | Org switching | Sidebar switcher; session-scoped current org |
| **Accounts** | Four account types | Asset, Liability, Revenue, Expense |
| **Accounts** | Currency support | Per-account currency field |
| **Accounts** | Running balance | Computed from transactions, never stored |
| **Accounts** | CSV export | Streamed download of all transactions |
| **Transactions** | Credit / debit entry | With description, date, and type |
| **Transactions** | Filters | Description search, type, date range — URL-bound |
| **Transactions** | CSV import | Paste/upload, preview, deduplicate |
| **Transactions** | AI explanation | Per-transaction natural-language explain |
| **Transactions** | AI categorization | Batch-categorize via `TransactionCategorizer` agent |
| **Reconciliation** | Date-range run | Detect credit/debit imbalances |
| **Reconciliation** | AI analysis | `ReconciliationAnalyst` explains each mismatch |
| **Reconciliation** | Issue lifecycle | Open → Resolved / Ignored |
| **Webhooks** | Multi-provider ingestion | `POST /webhooks/{provider}` |
| **Webhooks** | Idempotency | Deduplicated by `provider_event_id` |
| **Webhooks** | Signature validation | Stripe HMAC-SHA256; extensible per provider |
| **Webhooks** | Async processing | `ProcessWebhookEvent` queued job |
| **Webhooks** | Replay | Owner-only re-queue for failed events |
| **Webhooks** | Rate limiting | Named `throttle:webhooks` limiter |
| **Audit** | Immutable audit log | Every mutation: actor, subject, event, metadata |
| **Audit** | AI call audit | Agent name, model, prompt/completion token counts |
| **Audit** | Filterable viewer | `/audit-log` — filter by event name and actor |
| **Operations** | Queue dashboard | `/horizon` — Horizon job monitoring |
| **Operations** | Performance monitoring | `/pulse` — P95 response times, slow queries |
| **Operations** | Request inspector | `/telescope` — non-production request debugger |
| **Operations** | Health check | `GET /health` — DB, cache, queue status |
| **Operations** | JSON structured logs | `LOG_CHANNEL=json` for log aggregators |
| **AI** | Laravel AI SDK | Four agents, all independently fakeable |
| **AI** | Rate limiting | Named `throttle:ai` (10 req/min) |
| **MCP** | Read-only server | `GET /mcp/ledgerflow` — 4 org-scoped tools |
| **Dev** | Dev dashboard | `/dev` — live DB counts, recent activity, config |

---

## Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Language | PHP | 8.4 |
| Framework | Laravel | 13 |
| Frontend UI | Livewire | 4 |
| Component library | Flux UI | 2 |
| CSS | Tailwind CSS | 4 |
| Asset bundler | Vite | 8 |
| Database | PostgreSQL | 17+ |
| Cache / Queues | Redis | 7 |
| Queue dashboard | Laravel Horizon | 5 |
| Performance monitor | Laravel Pulse | 1 |
| Request inspector | Laravel Telescope | 5 (non-prod) |
| Auth backend | Laravel Fortify | 1 |
| AI SDK | `laravel/ai` | 0.x |
| MCP server | `laravel/mcp` | 0.x |
| Dev environment | Laravel Sail | 1 (Docker) |
| Test runner | Pest | 4 |
| Static analysis | PHPStan / Larastan | Level 5 |
| Code style | Laravel Pint | 1 |
| CI | GitHub Actions | — |

---

## Architecture at a Glance

```
Browser / AI Client
      │
      ▼
┌─────────────────────────────────────┐
│  HTTP Layer (Livewire pages +        │
│  controllers + MCP endpoint)         │
└─────────────────────────────────────┘
      │  thin — validate, authorize, delegate
      ▼
┌─────────────────────────────────────┐
│  Domain Actions                      │
│  CreateOrganization · RecordTx       │
│  RunReconciliation · SwitchOrg       │
└─────────────────────────────────────┘
      │  write audit events, dispatch jobs
      ▼
┌───────────────┐   ┌──────────────────┐
│  PostgreSQL   │   │  Redis Queue     │
│  (all state)  │   │  ProcessWebhook  │
│               │   │  ReconcileOrg    │
└───────────────┘   └──────────────────┘
                           │
                           ▼
                  ┌──────────────────┐
                  │  Laravel AI SDK  │
                  │  4 agents        │
                  │  (audited calls) │
                  └──────────────────┘
```

**Key design rules:**
- Controllers and Livewire components never touch Eloquent directly — all writes go through actions.
- Policies enforce org-scoping on every model — cross-org data leaks are impossible by design.
- No floats for money — all amounts are stored as minor-unit integers.
- Webhook events are idempotent — replaying the same event is safe.

---

## Quick Start (new dev)

### Prerequisites

| Tool | Version | Notes |
|------|---------|-------|
| Docker Desktop | ≥ 4.x | Required for Sail |
| Git | any | — |
| Composer | ≥ 2 | Required to install Sail before Docker is up |
| (optional) PHP 8.4 | local | Only needed for the initial `composer install` |

> **Windows users:** Use WSL 2 and run all commands inside the WSL terminal.

---

### Step 1 — Clone and install

```bash
git clone https://github.com/webgrip/ledgerflow.git
cd ledgerflow

# Install PHP deps locally (needed to get Sail before Docker is running)
composer install
```

### Step 2 — Configure environment

```bash
cp .env.example .env
```

Open `.env` and set your AI provider key (required for AI features):

```dotenv
# OpenAI (default provider)
OPENAI_API_KEY=sk-...

# Or Anthropic
ANTHROPIC_API_KEY=sk-ant-...
```

Everything else works out of the box with the Sail Docker defaults.

### Step 3 — Start services

```bash
# Start all Docker containers (Postgres, Redis, Mailpit)
vendor/bin/sail up -d

# Verify all containers are running
vendor/bin/sail ps
```

### Step 4 — Database setup

```bash
# Generate app key + run all migrations
vendor/bin/sail artisan key:generate
vendor/bin/sail artisan migrate

# Seed demo data (3 users, 2 orgs, sample transactions)
vendor/bin/sail artisan e2e:seed
```

### Step 5 — Build frontend assets

```bash
vendor/bin/sail npm install
vendor/bin/sail npm run build
```

### Step 6 — Start the full dev stack

```bash
# Starts: PHP server · queue worker · log tail · Vite dev server
vendor/bin/sail composer run dev
```

> The app is now available at **http://localhost**

---

### One-liner setup (for subsequent installs)

```bash
composer install && cp .env.example .env && vendor/bin/sail up -d \
  && vendor/bin/sail artisan key:generate \
  && vendor/bin/sail artisan migrate \
  && vendor/bin/sail artisan e2e:seed \
  && vendor/bin/sail npm install \
  && vendor/bin/sail npm run build
```

---

### Troubleshooting

| Symptom | Fix |
|---------|-----|
| `vendor/bin/sail: No such file` | Run `composer install` first (without Sail) |
| Port 80 already in use | Stop local nginx/Apache or change `APP_PORT` in `.env` |
| `SQLSTATE: connection refused` | Run `vendor/bin/sail up -d` — Postgres container is not running |
| Assets not loading (Vite error) | Run `vendor/bin/sail npm run build` |
| AI features return errors | Check `OPENAI_API_KEY` in `.env` |
| Queued jobs not processing | Run `vendor/bin/sail artisan horizon` in a separate terminal |
| Tests fail with migration errors | Run `vendor/bin/sail artisan migrate --env=testing` |

---

## Environment Variables

Full list of variables recognized by the application.

### Required for AI features

| Variable | Example | Description |
|----------|---------|-------------|
| `OPENAI_API_KEY` | `sk-...` | OpenAI API key (default provider) |
| `ANTHROPIC_API_KEY` | `sk-ant-...` | Anthropic (optional failover) |

### Webhook signing secrets

| Variable | Example | Description |
|----------|---------|-------------|
| `WEBHOOK_SECRET_STRIPE` | `whsec_...` | Stripe webhook signing secret |
| `WEBHOOK_SECRET_MOLLIE` | `...` | Mollie signing secret (stub) |

> Leave empty (`""`) in local dev — validation is skipped when the secret is blank.

### App

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_NAME` | `Laravel` | Application name |
| `APP_ENV` | `local` | `local`, `staging`, `production` |
| `APP_DEBUG` | `true` | Set to `false` in production |
| `APP_URL` | `http://localhost` | Full base URL |

### Database

| Variable | Sail default | Description |
|----------|-------------|-------------|
| `DB_CONNECTION` | `pgsql` | Must be `pgsql` (ADR-001) |
| `DB_HOST` | `pgsql` | Sail service name |
| `DB_PORT` | `5432` | — |
| `DB_DATABASE` | `laravel` | Database name |
| `DB_USERNAME` | `sail` | — |
| `DB_PASSWORD` | `password` | — |

### Cache & Queues

| Variable | Sail default | Description |
|----------|-------------|-------------|
| `CACHE_STORE` | `redis` | Use `redis` for multi-process safety |
| `QUEUE_CONNECTION` | `redis` | Required for Horizon |
| `REDIS_HOST` | `redis` | Sail service name |

---

## Seeded Demo Data

The `e2e:seed` command creates three users across two organizations.

| User | Email | Password | Role | Org |
|------|-------|----------|------|-----|
| Alice Founder | `alice@demo.test` | `password` | **Owner** | Acme Corp |
| Bob Accountant | `bob@demo.test` | `password` | Member | Acme Corp |
| Carol CFO | `carol@demo.test` | `password` | **Owner** | Globex LLC |

Each org is seeded with:
- 3 accounts (Checking, Revenue, Expenses)
- 10 transactions with mixed credits and debits
- 1 completed reconciliation run
- Sample audit events

---

## Available Routes

### Public

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/` | Welcome / landing page |
| `GET` | `/health` | JSON health check: DB, cache, queue |
| `GET` | `/dev` | Dev dashboard (public in demo mode) |
| `POST` | `/webhooks/{provider}` | Webhook ingestion (CSRF-exempt) |

### Auth (Fortify)

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/login` | Login form |
| `POST` | `/login` | Authenticate |
| `GET` | `/register` | Registration form |
| `POST` | `/register` | Create account |
| `POST` | `/logout` | Destroy session |
| `GET` | `/forgot-password` | Password reset request |
| `GET` | `/reset-password/{token}` | Password reset form |

### Authenticated App

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/dashboard` | Home dashboard |
| `GET` | `/organizations/create` | Create a new organization |
| `GET` | `/accounts` | Account list |
| `GET` | `/accounts/create` | New account form |
| `GET` | `/accounts/{account}` | Account detail + transaction list |
| `GET` | `/accounts/{account}/transactions/create` | New transaction form |
| `GET` | `/accounts/{account}/export` | Download CSV of all transactions |
| `GET` | `/reconciliation` | Reconciliation run list |
| `GET` | `/reconciliation/{run}` | Reconciliation run detail + AI analysis |
| `GET` | `/audit-log` | Filterable audit event viewer |
| `GET` | `/webhooks` | Webhook event list + replay |

### Operations

| Method | Path | Access |
|--------|------|--------|
| `GET` | `/horizon` | Auth required |
| `GET` | `/pulse` | Auth required |
| `GET` | `/telescope` | Non-production only |
| `GET` | `/mcp/ledgerflow` | Auth required; MCP JSON-RPC |

---

## Developer Tools

| URL | Tool | Description |
|-----|------|-------------|
| http://localhost/dev | Dev Dashboard | Live DB row counts, recent events, config overview, seeded credentials |
| http://localhost/horizon | Laravel Horizon | Queue worker supervision, job throughput, failed jobs |
| http://localhost/pulse | Laravel Pulse | P50/P95 response times, slow queries, exceptions, cache hits |
| http://localhost/telescope | Laravel Telescope | Full request/query/job/log inspector (local only) |
| http://localhost/health | Health Check | `{"status":"ok","checks":{...}}` — suitable for uptime monitors |
| http://localhost:8025 | Mailpit | Catch-all email inbox for outgoing mail in dev |

---

## Running Tests

### All tests

```bash
vendor/bin/sail artisan test --compact
```

### Filter by file or test name

```bash
# Single file
vendor/bin/sail artisan test --compact tests/Feature/ReconciliationTest.php

# By name pattern
vendor/bin/sail artisan test --compact --filter="it allows an org member"
```

### Test layers

```bash
# Unit tests only
vendor/bin/sail artisan test --compact tests/Unit/

# Feature tests only
vendor/bin/sail artisan test --compact tests/Feature/

# Smoke tests (health, negation cases)
vendor/bin/sail artisan test --compact tests/Smoke/

# Contract tests
vendor/bin/sail artisan test --compact tests/Contract/

# Integration tests
vendor/bin/sail artisan test --compact tests/Integration/
```

### E2E (Playwright)

```bash
# Install browsers once
npx playwright install --with-deps

# Run all E2E tests (headless)
npx playwright test

# Run with UI
npx playwright test --ui

# View last HTML report
npx playwright show-report playwright-report
```

### Performance tests (k6)

```bash
# Install k6: https://k6.io/docs/get-started/installation/
k6 run tests/performance/load.js
```

### Test counts (approximate)

| Layer | Count |
|-------|-------|
| Unit | ~20 |
| Feature | ~160 |
| Integration | ~10 |
| Contract | ~10 |
| Functional | ~10 |
| Smoke | ~10 |
| E2E (Playwright) | 102 |

---

## Code Quality

```bash
# Code style — auto-fix
vendor/bin/sail bin pint

# Code style — check only (CI mode)
vendor/bin/sail bin pint --test

# Static analysis (PHPStan level 5)
vendor/bin/sail bin phpstan analyse --no-progress

# Dependency vulnerability audit
composer audit
```

All three run automatically in GitHub Actions on every push to `main`, `master`, and `develop`.

---

## Project Structure

```
ledgerflow/
├── app/
│   ├── Actions/                   Domain actions (all state changes go through here)
│   │   ├── CreateOrganization.php
│   │   ├── CreateAccount.php
│   │   ├── RecordTransaction.php
│   │   ├── RunReconciliation.php
│   │   └── SwitchOrganization.php
│   │
│   ├── Ai/
│   │   └── Agents/                Laravel AI SDK agents
│   │       ├── TransactionExplainer.php    — explain a single transaction
│   │       ├── TransactionCategorizer.php  — structured output: category + confidence
│   │       ├── AccountActivitySummarizer.php — natural-language account summary
│   │       └── ReconciliationAnalyst.php   — explain a reconciliation mismatch
│   │
│   ├── Console/Commands/
│   │   └── E2eSeedCommand.php     — seeds demo data for local dev and E2E tests
│   │
│   ├── Enums/                     Typed PHP 8 enums
│   │   ├── AccountType.php        — Asset | Liability | Revenue | Expense
│   │   ├── TransactionType.php    — Credit | Debit
│   │   ├── ReconciliationStatus.php
│   │   ├── WebhookEventStatus.php
│   │   └── OrganizationRole.php   — Owner | Member
│   │
│   ├── Http/Controllers/
│   │   ├── AccountExportController.php   — streams CSV download
│   │   ├── HealthController.php          — JSON health check
│   │   └── WebhookController.php         — ingestion + HMAC validation
│   │
│   ├── Jobs/
│   │   ├── ProcessWebhookEvent.php       — idempotent async processing
│   │   └── ReconcileOrganization.php     — async reconciliation
│   │
│   ├── Mcp/
│   │   ├── LedgerFlowServer.php          — MCP server registration
│   │   └── Tools/                        — 4 read-only org-scoped tools
│   │       ├── GetAccountSummaryTool.php
│   │       ├── SearchTransactionsTool.php
│   │       ├── ListReconciliationIssuesTool.php
│   │       └── ListAuditEventsTool.php
│   │
│   ├── Models/                    Eloquent models (no business logic)
│   │   ├── User.php
│   │   ├── Organization.php
│   │   ├── OrganizationMembership.php
│   │   ├── Account.php
│   │   ├── Transaction.php
│   │   ├── ReconciliationRun.php
│   │   ├── ReconciliationIssue.php
│   │   ├── WebhookEvent.php
│   │   └── AuditEvent.php
│   │
│   ├── Policies/                  Authorization — all org-scoped
│   │   ├── AccountPolicy.php
│   │   └── TransactionPolicy.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php         — rate limiters, bindings
│   │   ├── FortifyServiceProvider.php     — auth customization
│   │   ├── HorizonServiceProvider.php     — Horizon auth gate
│   │   ├── PulseServiceProvider.php       — Pulse auth gate
│   │   └── TelescopeServiceProvider.php   — Telescope (non-prod gate)
│   │
│   └── Services/
│       └── AuditLogger.php        — write to audit_events; logAiCall() records tokens
│
├── database/
│   ├── factories/                 One factory per model
│   ├── migrations/                Ordered, timestamped
│   └── seeders/
│       └── DatabaseSeeder.php     (empty; use artisan e2e:seed)
│
├── resources/views/
│   ├── pages/                     Livewire Volt single-file components
│   │   ├── accounts/              index, create, show (with CSV import/export)
│   │   ├── transactions/          create
│   │   ├── reconciliation/        index, show (with AI analysis)
│   │   ├── audit/                 index (filterable)
│   │   ├── webhooks/              index (with replay)
│   │   ├── organizations/         create
│   │   └── dev/                   dashboard
│   │
│   ├── livewire/
│   │   └── transactions/
│   │       ├── explain-button.blade.php   — AI explain with audit logging
│   │       └── csv-import.blade.php       — import modal with preview/dedup
│   │
│   └── layouts/
│       └── app/
│           ├── sidebar.blade.php   — navigation + org switcher
│           └── header.blade.php
│
├── routes/
│   ├── web.php       — all web routes (public, auth, operations)
│   ├── ai.php        — AI agent routes (if any)
│   ├── console.php   — scheduled commands
│   └── settings.php  — Fortify settings routes
│
├── tests/
│   ├── Unit/          Isolated class/function tests
│   ├── Feature/       HTTP + Livewire integration tests (main suite)
│   ├── Integration/   Multi-component integration tests
│   ├── Contract/      Resource shape contracts
│   ├── Functional/    Request/response flow tests
│   ├── Smoke/         Health checks + negation tests
│   ├── E2e/           Playwright browser tests (102 tests)
│   ├── behavioral/    Gherkin .feature specifications
│   ├── manual/        .http files for manual API testing
│   └── performance/   k6 load test scripts
│
├── docs/
│   ├── adr/           Architecture Decision Records (ADR-001–004)
│   ├── architecture.md
│   ├── ai-strategy.md
│   ├── setup.md
│   └── demo-script.md
│
├── planning/
│   ├── roadmap.md     8-phase plan with rationale
│   ├── milestones.md  M0–M17, deliverables, status
│   ├── backlog.md     Full ✅/⬜ backlog
│   ├── priority.md    Quick-reference ordered todo list
│   └── ecosystem.md   Full tool ecosystem graph with ✅/📋/💡/❌ status
│
├── .github/
│   └── workflows/
│       └── tests.yml  CI pipeline
│
├── compose.yaml       Docker Compose (Sail): PHP, Postgres, Redis, Mailpit
└── phpstan.neon       Static analysis config
```

---

## Architecture Decisions

| ADR | Decision | Rationale |
|-----|---------|-----------|
| [ADR-001](docs/adr/001-postgresql.md) | PostgreSQL only | ILIKE, advisory locks, JSON operators — SQLite can't do fintech |
| [ADR-002](docs/adr/002-livewire-flux.md) | Livewire 4 + Flux UI | Server-side domain logic without SPA complexity |
| [ADR-003](docs/adr/003-laravel-ai-sdk.md) | Laravel AI SDK | First-party SDK; provider-agnostic; fakeable in tests |
| [ADR-004](docs/adr/004-idempotent-webhooks.md) | Idempotent webhooks | Provider deduplication key; async job; replay safe |

---

## AI Features

All AI features use the [Laravel AI SDK](https://github.com/laravel/ai) (`laravel/ai`).

### Agents

| Agent | Class | Trigger | SDK feature |
|-------|-------|---------|-------------|
| Transaction Explainer | `TransactionExplainer` | Explain button on transaction | `Promptable` |
| Transaction Categorizer | `TransactionCategorizer` | Bulk categorize | `HasStructuredOutput` |
| Account Summarizer | `AccountActivitySummarizer` | Account detail page | `Promptable`, `#[UseCheapestModel]` |
| Reconciliation Analyst | `ReconciliationAnalyst` | Reconciliation show page | `Promptable` |

### Usage auditing

Every AI call is logged to `audit_events`:
```json
{
  "event": "ai.agent_called",
  "metadata": {
    "agent": "TransactionExplainer",
    "model": "gpt-4o-mini",
    "prompt_tokens": 312,
    "completion_tokens": 87
  }
}
```

### Rate limiting

AI endpoints are protected by a named `throttle:ai` limiter (10 requests/minute per user).

### Testing AI features

All agents support `Agent::fake()`:
```php
TransactionExplainer::fake(['This is a payroll credit.']);
// ... trigger the feature ...
TransactionExplainer::assertPrompted();
```

---

## MCP Integration

LedgerFlow exposes a read-only [Model Context Protocol](https://modelcontextprotocol.io/) server,
making it compatible with any MCP-capable AI client.

**Endpoint:** `GET /mcp/ledgerflow` (requires authentication)

### Available Tools

| Tool | Description | Parameters |
|------|-------------|-----------|
| `get-account-summary` | Lists accounts with balances | — |
| `search-transactions` | Filter transactions | `query`, `type`, `from`, `to` |
| `list-reconciliation-issues` | Issues by status | `status` (`open`\|`resolved`\|`ignored`) |
| `list-audit-events` | Recent audit trail | `event` (filter by event name) |

All tools are automatically scoped to the authenticated user's current organization. Cross-org data
access is impossible.

### Connecting an AI client

**VS Code (Copilot Chat)** — add to your MCP settings:
```json
{
  "mcp": {
    "servers": {
      "ledgerflow": {
        "url": "http://localhost/mcp/ledgerflow",
        "headers": { "Cookie": "<your-session-cookie>" }
      }
    }
  }
}
```

**Cursor** — add to `.cursor/mcp.json`:
```json
{
  "mcpServers": {
    "ledgerflow": {
      "url": "http://localhost/mcp/ledgerflow"
    }
  }
}
```

---

## Webhook Integration

`POST /webhooks/{provider}` accepts events from any payment provider.

### Supported providers

| Provider | Signature validation | Config key |
|----------|--------------------|-----------:|
| Stripe | HMAC-SHA256 | `WEBHOOK_SECRET_STRIPE` |
| Mollie | stub | `WEBHOOK_SECRET_MOLLIE` |

### Processing pipeline

```
POST /webhooks/stripe
  → WebhookController::receive()
      → validate Stripe-Signature header (HMAC-SHA256)
      → deduplicate on provider_event_id (upsert)
      → dispatch ProcessWebhookEvent job
          → update WebhookEvent status (processed/failed)
          → write audit_events record
```

### Testing locally with Stripe CLI

```bash
# Install Stripe CLI, then forward events to your local app
stripe listen --forward-to http://localhost/webhooks/stripe
stripe trigger payment_intent.succeeded
```

---

## Contributing

This is a portfolio project but PRs are welcome for bugs and improvements.

```bash
# Fork, clone, branch
git checkout -b feat/your-feature

# Make changes, write tests
vendor/bin/sail artisan test --compact

# Code style
vendor/bin/sail bin pint

# Static analysis
vendor/bin/sail bin phpstan analyse

# Commit (Conventional Commits required)
git commit -m "feat(accounts): add monthly period close"

# Push and open PR against main
```

**Commit message format:** `<type>(<scope>): <description>`
Types: `feat`, `fix`, `docs`, `test`, `refactor`, `chore`, `ci`

---

## Roadmap

See [`planning/roadmap.md`](planning/roadmap.md) for the full 8-phase plan.

Next up (**Phase 4 — Supply Chain & Standards**):
- `composer audit` in CI
- `.editorconfig`
- `SECURITY.md`
- CycloneDX SBOM (`bom.json`)
- `CHANGELOG.md` + `git-cliff`
- `.releaserc` + semantic-release
- Rector for automated PHP upgrades
- PHPStan level 6

Then **Phase 5 — Public REST API** (Sanctum, `/api/v1/`, OpenAPI spec).

---

## License

MIT — see [LICENSE](LICENSE).
