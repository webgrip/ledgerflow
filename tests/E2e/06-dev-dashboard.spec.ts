/**
 * 06 — Dev dashboard flows
 */
import { test, expect } from '@playwright/test';

test.describe('Dev dashboard — public access', () => {
  test('is accessible without authentication', async ({ page }) => {
    await page.goto('/dev');
    await expect(page).toHaveURL('/dev');
    await expect(page.getByText(/Development Dashboard/i)).toBeVisible();
  });

  test('shows a development environment banner', async ({ page }) => {
    await page.goto('/dev');
    await expect(page.getByText(/Development Dashboard|development|dev dashboard/i).first()).toBeVisible();
  });
});

test.describe('Dev dashboard — KPI cards', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/dev');
    await page.waitForLoadState('networkidle');
  });

  test('Users KPI card is visible', async ({ page }) => {
    await expect(page.getByText(/Users/i).first()).toBeVisible();
  });

  test('Organizations KPI card is visible', async ({ page }) => {
    await expect(page.getByText(/Orgs/i).first()).toBeVisible();
  });

  test('Accounts KPI card is visible', async ({ page }) => {
    await expect(page.getByText(/Accounts/i).first()).toBeVisible();
  });

  test('Transactions KPI card is visible', async ({ page }) => {
    await expect(page.getByText(/Transactions/i).first()).toBeVisible();
  });

  test('Net Flow KPI card is visible', async ({ page }) => {
    await expect(page.getByText(/net flow/i).first()).toBeVisible();
  });

  test('KPI stats show numeric values after seeding', async ({ page }) => {
    await expect(page.getByText(/\d+/).first()).toBeVisible();
  });
});

test.describe('Dev dashboard — tab navigation', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/dev');
    await page.waitForLoadState('networkidle');
  });

  test('all 6 tabs are visible', async ({ page }) => {
    await expect(page.getByRole('button', { name: /overview/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /orgs/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /accounts/i }).first()).toBeVisible();
    await expect(page.getByRole('button', { name: /transactions/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /users/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /system/i })).toBeVisible();
  });

  test('Overview tab is active by default', async ({ page }) => {
    await expect(page.getByText(/overview|recent|account type/i).first()).toBeVisible();
  });

  test('Orgs tab shows organization cards', async ({ page }) => {
    await page.getByRole('button', { name: /orgs/i }).click();
    await expect(page.getByText(/Acme Corp/i).first()).toBeVisible();
  });

  test('Accounts tab shows accounts table', async ({ page }) => {
    await page.getByRole('button', { name: /accounts/i }).first().click();
    await expect(page.getByText(/Balance|Name|Type/i).first()).toBeVisible();
  });

  test('Transactions tab has a search input', async ({ page }) => {
    await page.getByRole('button', { name: /transactions/i }).click();
    await expect(page.getByPlaceholder(/search|filter/i)).toBeVisible();
  });

  test('Users tab shows user list', async ({ page }) => {
    await page.getByRole('button', { name: /users/i }).click();
    await expect(page.getByText(/alice@demo\.test/i).first()).toBeVisible();
  });

  test('System tab shows environment info and route table', async ({ page }) => {
    await page.getByRole('button', { name: /system/i }).click();
    await expect(page.getByText(/Environment/i).first()).toBeVisible();
    await expect(page.getByText(/Routes/i).first()).toBeVisible();
  });
});

test.describe('Dev dashboard — quick actions', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/dev');
    await page.waitForLoadState('networkidle');
  });

  test('"Seed Demo Data" button is enabled', async ({ page }) => {
    const btn = page.getByRole('button', { name: /seed demo data/i });
    await expect(btn).toBeVisible();
    await expect(btn).toBeEnabled();
  });

  test('"Nuke Database" button is enabled', async ({ page }) => {
    const btn = page.getByRole('button', { name: /nuke database/i });
    await expect(btn).toBeVisible();
    await expect(btn).toBeEnabled();
  });

  test('"Nuke Database" shows a browser confirmation dialog', async ({ page }) => {
    let dialogMessage = '';
    page.once('dialog', async (dialog) => {
      dialogMessage = dialog.message();
      await dialog.dismiss();
    });

    await page.getByRole('button', { name: /nuke database/i }).click();
    await page.waitForTimeout(500);
    expect(dialogMessage.length).toBeGreaterThan(0);
  });

  test('cancelling the nuke dialog leaves data intact', async ({ page }) => {
    page.once('dialog', async (dialog) => await dialog.dismiss());
    await page.getByRole('button', { name: /nuke database/i }).click();
    await page.waitForTimeout(500);
    await expect(page.getByText(/\d+/).first()).toBeVisible();
  });

  test('Transactions search filters results', async ({ page }) => {
    await page.getByRole('button', { name: /transactions/i }).click();
    const searchInput = page.getByPlaceholder(/search|filter/i);
    await searchInput.fill('zzz-nonexistent-query-zzz');
    await page.waitForTimeout(600);

    const rows = page.locator('table tbody tr');
    const count = await rows.count();
    if (count > 0) {
      await expect(page.getByText(/no.*found|no transactions/i).first()).toBeVisible();
    } else {
      expect(count).toBe(0);
    }
  });
});

test.describe('Dev dashboard — auto-refresh', () => {
  test('auto-refresh toggle is present', async ({ page }) => {
    await page.goto('/dev');
    await expect(page.getByText(/auto-refresh|auto refresh/i)).toBeVisible();
  });
});
