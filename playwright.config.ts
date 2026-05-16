import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright configuration for LedgerFlow E2E tests.
 *
 * Run: npx playwright test
 * UI mode: npx playwright test --ui
 *
 * The app must be running at APP_URL (default: http://localhost:8003).
 * With Sail: vendor/bin/sail up -d && npx playwright test
 */
export default defineConfig({
  testDir: './tests/E2e',
  fullyParallel: false, // keep sequential — tests may share DB state
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: [['html', { open: 'never' }], ['line']],

  use: {
    baseURL: process.env.APP_URL ?? 'http://localhost:8003',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
