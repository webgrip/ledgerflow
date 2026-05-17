# Setup

LedgerFlow is built around Laravel Sail. Local PHP, Composer, and Node installations are useful for bootstrapping, but day-to-day commands should run through Sail.

## Prerequisites

- Docker Desktop or a compatible Docker runtime
- Composer 2
- Git
- Optional local PHP 8.4 for the first `composer install`

## Initial setup

```bash
git clone https://github.com/webgrip/ledgerflow.git
cd ledgerflow
composer install
cp .env.example .env
vendor/bin/sail up -d
vendor/bin/sail artisan key:generate
vendor/bin/sail artisan migrate
vendor/bin/sail artisan e2e:seed
vendor/bin/sail npm install
vendor/bin/sail npm run build
```

## Daily development

```bash
vendor/bin/sail up -d
vendor/bin/sail composer run dev
```

The development stack starts the HTTP server, queue listener, log tail, and Vite dev server.

## Useful commands

| Task | Command |
| --- | --- |
| Run all tests | `vendor/bin/sail artisan test --compact` |
| Run a filtered test | `vendor/bin/sail artisan test --compact --filter="name"` |
| Format PHP | `vendor/bin/sail bin pint` |
| Check PHP formatting | `vendor/bin/sail bin pint --test` |
| Static analysis | `vendor/bin/sail bin phpstan analyse --no-progress` |
| Build assets | `vendor/bin/sail npm run build` |
| Run E2E tests | `vendor/bin/sail npm run test:e2e` |

## Required CI secrets

Flux UI is a private Composer dependency. CI needs these repository or environment secrets:

- `FLUX_USERNAME`
- `FLUX_LICENSE_KEY`

Semantic release uses the built-in `GITHUB_TOKEN` for tags, release notes, GitHub releases, and the release commit on `main`.
