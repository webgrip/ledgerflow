import { test, expect, Page } from '@playwright/test';

/**
 * Transaction recording E2E tests.
 * Assumes alice@demo.test and at least one account exist (seed via /dev).
 */

async function loginAs(page: Page, email = 'alice@demo.test', password = 'password') {
  await page.goto('/login');
  await page.getByRole('textbox', { name: /email/i }).fill(email);
  await page.getByRole('textbox', { name: /password/i }).fill(password);
  await page.getByRole('button', { name: /log in/i }).click();
  await page.waitForURL(/(?!.*login)/);
}

test.describe('Recording transactions', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page);
  });

  test('can navigate to record transaction from the account detail page', async ({ page }) => {
    await page.goto('/accounts');

    const firstRow = page.locator('table tbody tr').first();
    if (await firstRow.count() === 0) {
      test.skip();
    }

    await firstRow.click();
    await expect(page).toHaveURL(/accounts\/\d+/);

    const recordBtn = page.getByRole('link', { name: /record transaction/i });
    await expect(recordBtn).toBeVisible();
    await recordBtn.click();

    await expect(page).toHaveURL(/transactions\/create/);
  });

  test('can record a credit transaction', async ({ page }) => {
    // Navigate to first available account
    await page.goto('/accounts');
    const firstRow = page.locator('table tbody tr').first();
    if (await firstRow.count() === 0) test.skip();

    await firstRow.click();
    const currentUrl = page.url();
    const accountId = currentUrl.match(/accounts\/(\d+)/)?.[1];
    if (!accountId) test.skip();

    await page.goto(`/accounts/${accountId}/transactions/create`);

    await page.getByLabel(/type/i).selectOption('credit');
    await page.getByLabel(/amount/i).fill('123.45');
    await page.getByLabel(/description/i).fill(`E2E credit ${Date.now()}`);
    // Date is pre-filled with today

    await page.getByRole('button', { name: /record/i }).click();

    // Should redirect back to account detail
    await expect(page).toHaveURL(new RegExp(`accounts/${accountId}$`));
  });
});
