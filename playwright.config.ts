import { defineConfig, devices } from '@playwright/test';

/**
 * LedgerFlow - Playwright E2E test configuration.
 *
 * Run:
 *   npm run test:e2e            # headless, chromium
 *   npm run test:e2e:headed     # visible browser
 *   npm run test:e2e:ui         # interactive UI mode
 *
 * Prerequisites:
 *   1. `vendor/bin/sail up -d` (app running at APP_URL)
 *   2. The global setup seeds demo data and saves session storage states
 *      under storage/e2e/. Specs import these via test.use({ storageState })
 *      to avoid repeated logins that would trip Fortify's login throttle.
 */
export default defineConfig({
  testDir: './tests/E2e',
  fullyParallel: false,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: [
    ['html', { open: 'never', outputFolder: 'playwright-report' }],
    ['line'],
  ],

  use: {
    baseURL: process.env.APP_URL ?? 'http://localhost',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    actionTimeout: 10_000,
    navigationTimeout: 20_000,
  },

  projects: [
    // Seed DB and save alice/bob/carol session files
    { name: 'setup', testMatch: '**/global-setup.ts' },

    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
      dependencies: ['setup'],
    },
  ],
});
