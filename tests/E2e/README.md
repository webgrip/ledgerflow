# E2E Tests — Playwright

End-to-end tests using [Playwright](https://playwright.dev).

## Prerequisites

1. **App running**: `vendor/bin/sail up -d`
2. **Playwright + browser installed**: `npm install && npx playwright install chromium`

## Run

```bash
npm run test:e2e                # headless, all specs
npm run test:e2e:headed         # visible browser
npm run test:e2e:ui             # interactive UI mode with time-travel
npm run test:e2e:report         # open last HTML report
npx playwright test 03-accounts # run a single spec file
```

## Structure

| File | Flows covered |
|------|---------------|
| `global-setup.ts` | Seeds demo data (alice/bob/carol + orgs + accounts + transactions) once before all tests |
| `helpers.ts` | Shared `loginAs`, `createAccount`, `recordTransaction` helpers |
| `01-auth.spec.ts` | Login, logout, registration, auth guards, validation errors |
| `02-organizations.spec.ts` | Create org, org switcher, switch between orgs, access control |
| `03-accounts.spec.ts` | Accounts list, create all 5 types, detail page, balance display, access control |
| `04-transactions.spec.ts` | Record credit/debit, balance calculation, validation, access control |
| `05-ai-explain.spec.ts` | Explain button, loading state, response display, dismiss |
| `06-dev-dashboard.spec.ts` | Public access, KPI cards, all 6 tabs, seed/nuke actions, TX search, auto-refresh |
| `07-navigation.spec.ts` | Sidebar links, user menu, breadcrumbs, 404 handling |

## Demo credentials (seeded by global-setup)

| User | Email | Password | Role |
|------|-------|----------|------|
| Alice | alice@demo.test | password | Owner — Acme Corp |
| Bob | bob@demo.test | password | Member — Acme Corp |
| Carol | carol@demo.test | password | Owner — Globex LLC |

## Configuration

See `playwright.config.ts` at the repo root.
Set `APP_URL` env var to point to a different app instance:

```bash
APP_URL=http://localhost:8080 npm run test:e2e
```
