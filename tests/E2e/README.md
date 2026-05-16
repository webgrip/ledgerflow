# E2E Tests — Playwright

## Setup

```bash
npm install --save-dev @playwright/test
npx playwright install chromium
```

## Run

```bash
# All E2E tests (app must be running)
npx playwright test

# With UI explorer
npx playwright test --ui

# Specific file
npx playwright test tests/E2e/login.spec.ts

# Against a specific base URL
APP_URL=http://localhost:8003 npx playwright test
```

## Prerequisites

1. Start the application: `vendor/bin/sail up -d`
2. Seed demo data: visit `/dev` and click **Seed Demo Data**
3. Run tests: `npx playwright test`

## Test files

| File | Coverage |
|------|----------|
| `login.spec.ts` | Login, logout, registration flows |
| `account-management.spec.ts` | Account index, create, detail page |
| `transaction.spec.ts` | Recording transactions from account detail |
| `dev-dashboard.spec.ts` | Dev dashboard tabs, seed, nuke |

## Demo credentials (after seeding)

| Email | Password | Role |
|-------|----------|------|
| alice@demo.test | password | Owner — Acme Corp |
| bob@demo.test | password | Member — Acme Corp |
| carol@demo.test | password | Owner — Globex LLC |
