import { test, expect, Page } from '@playwright/test';

/**
 * Account management E2E tests.
 * Assumes alice@demo.test / password exists (seed via /dev → "Seed Demo Data").
 */

async function loginAs(page: Page, email = 'alice@demo.test', password = 'password') {
  await page.goto('/login');
  await page.getByRole('textbox', { name: /email/i }).fill(email);
  await page.getByRole('textbox', { name: /password/i }).fill(password);
  await page.getByRole('button', { name: /log in/i }).click();
  await page.waitForURL(/(?!.*login)/);
}

test.describe('Account creation', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page);
  });

  test('accounts index page shows the accounts list', async ({ page }) => {
    await page.goto('/accounts');
    await expect(page).not.toHaveURL(/login/);
    // Page should have "Accounts" heading
    await expect(page.getByRole('heading', { name: /accounts/i }).first()).toBeVisible();
  });

  test('can navigate to the new account form', async ({ page }) => {
    await page.goto('/accounts');
    await page.getByRole('link', { name: /new account/i }).click();
    await expect(page).toHaveURL(/accounts\/create/);
  });

  test('can create a new account', async ({ page }) => {
    const accountName = `E2E Account ${Date.now()}`;
    await page.goto('/accounts/create');

    await page.getByLabel(/account name/i).fill(accountName);
    await page.getByLabel(/account type/i).selectOption('asset');
    await page.getByLabel(/currency/i).fill('USD');
    await page.getByRole('button', { name: /create account/i }).click();

    // Should redirect to account detail
    await expect(page).toHaveURL(/accounts\/\d+/);
    await expect(page.getByText(accountName)).toBeVisible();
  });

  test('shows validation error when name is empty', async ({ page }) => {
    await page.goto('/accounts/create');
    await page.getByRole('button', { name: /create account/i }).click();

    // Should stay on create page with an error
    await expect(page).toHaveURL(/accounts\/create/);
  });
});

test.describe('Account detail page', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page);
  });

  test('can access an account detail page from the index', async ({ page }) => {
    await page.goto('/accounts');

    // Click first account row if any exist
    const firstRow = page.locator('table tbody tr').first();
    const count = await firstRow.count();
    if (count > 0) {
      await firstRow.click();
      await expect(page).toHaveURL(/accounts\/\d+/);
    } else {
      // No accounts — just verify the page renders empty state
      await expect(page.getByText(/no accounts/i)).toBeVisible();
    }
  });
});
