/**
 * Global setup — runs once before all Playwright tests.
 *
 * 1. Truncates the DB and re-seeds deterministic demo data via Artisan.
 * 2. Logs in as each demo user in an isolated context and saves their
 *    session to storage/e2e/*.json so spec files can reuse sessions
 *    without re-logging in (which would trip Fortify's login throttle).
 *
 * NOTE: Each user must be saved in its own browser context so that
 *       logging out one user does NOT invalidate another's saved session.
 */
import { test as setup, expect, chromium } from '@playwright/test';
import { execSync } from 'child_process';
import fs from 'fs';

const SESSION_DIR = 'storage/e2e';

setup('seed demo data and save sessions', async () => {
  // ── 1. Fresh seed ─────────────────────────────────────────────────────────
  execSync('vendor/bin/sail artisan e2e:seed --fresh', {
    cwd: process.cwd(),
    stdio: 'inherit',
    timeout: 30_000,
  });

  // ── 2. Ensure session directory exists ────────────────────────────────────
  if (!fs.existsSync(SESSION_DIR)) {
    fs.mkdirSync(SESSION_DIR, { recursive: true });
  }

  // ── 3. Save sessions for each demo user in ISOLATED contexts ─────────────
  //       Using a separate context per user ensures that logging in as one
  //       user does not affect another user's saved session state.
  const browser = await chromium.launch();

  const users = [
    { email: 'alice@demo.test', file: `${SESSION_DIR}/alice.json` },
    { email: 'bob@demo.test',   file: `${SESSION_DIR}/bob.json` },
    { email: 'carol@demo.test', file: `${SESSION_DIR}/carol.json` },
  ];

  for (const user of users) {
    const context = await browser.newContext({ baseURL: 'http://localhost' });
    const page = await context.newPage();

    await page.goto('/login');
    await page.getByRole('textbox', { name: /email/i }).fill(user.email);
    await page.getByRole('textbox', { name: /password/i }).fill('password');
    await page.locator('[data-test="login-button"], button[type="submit"]').first().click();
    await expect(page).not.toHaveURL(/login/, { timeout: 15_000 });

    await context.storageState({ path: user.file });
    await context.close();
  }

  await browser.close();
});

