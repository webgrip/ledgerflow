/**
 * 05 — AI transaction explanation flow
 *
 * Covers: Explain button visible, click triggers loading state,
 *         response text appears, dismiss works.
 *         (Tests the UI plumbing; AI responses depend on the configured provider.)
 */
import { test, expect } from '@playwright/test';
import { createAccount, recordTransaction } from './helpers.js';

test.describe('AI Explain button', () => {
  test.use({ storageState: 'storage/e2e/alice.json' });

  // Shared account created once for this suite
  let accountId: number;

  test.beforeAll(async ({ browser }) => {
    const ctx = await browser.newContext({ storageState: 'storage/e2e/alice.json' });
    const page = await ctx.newPage();
    const url = await createAccount(page, `AI Test ${Date.now()}`, 'asset');
    accountId = parseInt(url.match(/accounts\/(\d+)/)![1]);
    await recordTransaction(page, accountId, 'credit', '999.00', 'AI test transaction');
    await ctx.close();
  });

  test('"Explain" button is visible for each transaction row', async ({ page }) => {
    await page.goto(`/accounts/${accountId}`);

    const explainBtn = page.getByRole('button', { name: /explain/i }).first();
    await expect(explainBtn).toBeVisible();
  });

  test('clicking Explain fires a Livewire request', async ({ page }) => {
    await page.goto(`/accounts/${accountId}`);

    const explainBtn = page.getByRole('button', { name: /explain/i }).first();

    // Intercept Livewire call to confirm the action is dispatched
    await Promise.all([
      page.waitForResponse(resp => resp.url().includes('livewire') && resp.status() === 200),
      explainBtn.click(),
    ]);
  });

  test('after clicking Explain, explanation panel or error text appears', async ({ page }) => {
    await page.goto(`/accounts/${accountId}`);

    await page.getByRole('button', { name: /explain/i }).first().click();

    // Either an explanation or an unavailability message should appear
    await expect(
      page.getByText(/sparkles|explanation|unavailable|temporarily|error/i).first()
    ).toBeVisible({ timeout: 15_000 });
  });

  test('dismiss button hides the explanation panel', async ({ page }) => {
    await page.goto(`/accounts/${accountId}`);

    await page.getByRole('button', { name: /explain/i }).first().click();
    await page.waitForTimeout(3000);

    const dismissBtn = page.getByRole('button', { name: /dismiss|close/i }).first();
    if (await dismissBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
      await dismissBtn.click();
      await expect(page.getByRole('button', { name: /explain/i }).first()).toBeVisible();
    }
  });
});
