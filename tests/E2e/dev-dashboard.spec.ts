import { test, expect } from '@playwright/test';

/**
 * Dev dashboard E2E tests.
 * The dev dashboard is publicly accessible — no auth required.
 */

test.describe('Dev dashboard', () => {
  test('is accessible without authentication', async ({ page }) => {
    await page.goto('/dev');
    await expect(page).toHaveURL('/dev');
    await expect(page.getByText(/Development Dashboard/)).toBeVisible();
  });

  test('shows the KPI stat cards', async ({ page }) => {
    await page.goto('/dev');
    await expect(page.getByText('Users')).toBeVisible();
    await expect(page.getByText('Accounts')).toBeVisible();
    await expect(page.getByText('Transactions')).toBeVisible();
  });

  test('shows tab navigation', async ({ page }) => {
    await page.goto('/dev');
    await expect(page.getByRole('button', { name: /overview/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /orgs/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /accounts/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /transactions/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /users/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /system/i })).toBeVisible();
  });

  test('can switch tabs', async ({ page }) => {
    await page.goto('/dev');
    await page.getByRole('button', { name: /system/i }).click();
    // System tab should show environment info
    await expect(page.getByText(/Environment/i)).toBeVisible();
  });

  test('seed demo data button exists and is clickable', async ({ page }) => {
    await page.goto('/dev');
    const seedBtn = page.getByRole('button', { name: /seed demo data/i });
    await expect(seedBtn).toBeVisible();
    await expect(seedBtn).toBeEnabled();
  });

  test('nuke database button requires confirmation', async ({ page }) => {
    await page.goto('/dev');
    const nukeBtn = page.getByRole('button', { name: /nuke database/i });
    await expect(nukeBtn).toBeVisible();

    // Dialog should appear
    page.once('dialog', async dialog => {
      expect(dialog.message()).toContain('DELETE ALL DATA');
      await dialog.dismiss(); // cancel
    });
    await nukeBtn.click();
  });
});
