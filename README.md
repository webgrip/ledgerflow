# LedgerFlow

A portfolio-grade Laravel fintech platform demonstrating modern PHP engineering patterns: clean architecture, domain actions, AI-powered features, webhook ingestion, reconciliation, and read-only MCP integration.

[![Tests](https://github.com/webgrip/ledgerflow/actions/workflows/tests.yml/badge.svg)](https://github.com/webgrip/ledgerflow/actions/workflows/tests.yml)

---

## What it does

| Feature | Details |
|---------|---------|
| **Multi-org workspace** | Users create/join organizations; role-based access (Owner / Member) |
| **Account management** | Asset, Liability, Revenue, Expense accounts with currency support |
| **Transaction recording** | Credit/debit with description, date, and real-time balance |
| **Transaction filters** | Search by description, type, and date range — URL-bound state |
| **Audit log** | Every mutation logged to `audit_events` with actor and subject |
| **Webhook ingestion** | Idempotent `POST /webhooks/{provider}` → queued `ProcessWebhookEvent` job |
| **Reconciliation** | Run reconciliation over a date range; AI explains each mismatch |
| **AI features** | Transaction explanation, categorization, account summary (Laravel AI SDK) |
| **MCP server** | Read-only `GET /mcp/ledgerflow` for AI clients (Cursor, VS Code, Claude Desktop) |
| **Operations** | Horizon queue dashboard, Pulse monitoring, `/health` endpoint, JSON logs |
| **Dev dashboard** | Single-page overview of all models, counts, and recent activity |

---

## Tech stack

| Layer | Choice |
|-------|--------|
| Framework | Laravel 13, PHP 8.4 |
| Frontend | Livewire 4 + Flux UI + Tailwind CSS v4 |
| Database | PostgreSQL 17 |
| Queue | Redis + Laravel Horizon |
| Monitoring | Laravel Pulse |
| AI | Laravel AI SDK (`laravel/ai`) |
| MCP | `laravel/mcp` (read-only) |
| Auth | Laravel Fortify |
| Testing | Pest 4 + PHPStan (level 5) + Pint |
| CI | GitHub Actions |

---

## Quick start

**Requirements:** Docker Desktop, Git

```bash
git clone https://github.com/webgrip/ledgerflow.git
cd ledgerflow

# Install PHP dependencies
composer install

# Copy environment and generate key
cp .env.example .env
php artisan key:generate

# Start Docker services
vendor/bin/sail up -d

# Run migrations and seed demo data
vendor/bin/sail artisan migrate
vendor/bin/sail artisan e2e:seed

# Start the queue worker (required for AI and webhook jobs)
vendor/bin/sail artisan horizon

# Build frontend assets
vendor/bin/sail npm run build
```

Then visit **http://localhost**.

---

## Demo credentials

| User | Email | Password | Role |
|------|-------|----------|------|
| Alice Founder | alice@demo.test | password | Owner (Acme Corp) |
| Bob Accountant | bob@demo.test | password | Member (Acme Corp) |
| Carol CFO | carol@demo.test | password | Owner (Globex LLC) |

---

## Developer tools

| URL | Description |
|-----|-------------|
| `/dev` | Dev dashboard — live DB counts, recent events |
| `/horizon` | Queue dashboard (Horizon) |
| `/pulse` | Performance monitoring (Pulse) |
| `/health` | JSON health check (DB, cache, queue) |
| `/mcp/ledgerflow` | Read-only MCP server for AI clients |

---

## Running tests

```bash
# All tests
vendor/bin/sail artisan test --compact

# Single file
vendor/bin/sail artisan test --compact tests/Feature/ReconciliationTest.php

# Static analysis
vendor/bin/sail bin phpstan analyse --no-progress

# Code style
vendor/bin/sail bin pint --dirty
```

---

## Project structure

```
app/
  Actions/          Domain actions (CreateOrganization, RecordTransaction, RunReconciliation)
  Ai/Agents/        Laravel AI SDK agents (Explainer, Categorizer, Summarizer, Analyst)
  Console/Commands/ CLI commands (e2e:seed)
  Enums/            Typed enums for status fields and business types
  Http/Controllers/ HTTP controllers (WebhookController, HealthController)
  Jobs/             Queue jobs (ProcessWebhookEvent, ReconcileOrganization)
  Mcp/              MCP server and read-only tools
  Models/           Eloquent models
  Policies/         Authorization policies
  Providers/        Service providers (App, Fortify, Horizon, Pulse)
  Services/         Infrastructure services (AuditLogger)

resources/views/
  pages/            Livewire page components (one file = PHP class + Blade template)
  livewire/         Reusable Livewire components
  layouts/          App layout with sidebar

database/
  migrations/       All migrations (ordered by timestamp)
  factories/        Factories for all models
  seeders/          DatabaseSeeder (empty; use e2e:seed for demo data)

docs/
  adr/              Architecture Decision Records
  architecture.md   System design overview
  setup.md          Detailed local setup guide
  ai-strategy.md    AI feature design principles
  demo-script.md    Guided demo walkthrough
```

---

## Architecture decisions

| ADR | Decision |
|-----|---------|
| [ADR-001](docs/adr/001-postgresql.md) | PostgreSQL as the only supported database |
| [ADR-002](docs/adr/002-livewire-flux.md) | Livewire 4 + Flux UI for the frontend |
| [ADR-003](docs/adr/003-laravel-ai-sdk.md) | Laravel AI SDK for all AI features |
| [ADR-004](docs/adr/004-idempotent-webhooks.md) | Idempotent webhook ingestion strategy |

---

## MCP integration

LedgerFlow exposes a read-only MCP server at `/mcp/ledgerflow`.

Available tools:
- `get-account-summary` — account list with balances
- `search-transactions` — filter by description, type, date range
- `list-reconciliation-issues` — open/resolved/ignored issues
- `list-audit-events` — recent audit trail

All tools are scoped to the authenticated user's current organization.

---

## License

MIT
