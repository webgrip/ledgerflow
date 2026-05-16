/**
 * 02 — Organization management flows
 */
import { execSync } from 'child_process';
import { test, expect } from '@playwright/test';
import { loginAs, BOB } from './helpers.js';

function resetAliceOrg() {
  execSync('vendor/bin/sail artisan e2e:reset-org', { cwd: process.cwd(), stdio: 'ignore' });
}

// The org-switcher trigger is a flux:sidebar.item rendered with data-flux-sidebar-item attr
const orgTrigger = (page: any, name: RegExp | string) =>
  page.locator('[data-flux-sidebar-item]').filter({ hasText: name }).first();

test.describe('Organization creation', () => {
  test.use({ storageState: 'storage/e2e/alice.json' });

  test('create organization page is accessible', async ({ page }) => {
    await page.goto('/organizations/create');
    await expect(page.locator('[data-flux-heading].text-2xl').first()).toBeVisible();
  });

  test('organization name field is present and required', async ({ page }) => {
    await page.goto('/organizations/create');
    await expect(page.getByLabel(/organization name/i)).toBeVisible();
    await expect(page.getByRole('button', { name: /create organization/i })).toBeVisible();
  });

  test('submitting empty name stays on the create page', async ({ page }) => {
    await page.goto('/organizations/create');
    await page.getByRole('button', { name: /create organization/i }).click();
    await page.waitForTimeout(800);
    await expect(page).toHaveURL(/organizations\/create/);
  });

  test('can create a new organization and is redirected to dashboard', async ({ page }) => {
    const orgName = `E2E Org ${Date.now()}`;
    await page.goto('/organizations/create');
    await page.getByLabel(/organization name/i).fill(orgName);
    await page.getByRole('button', { name: /create organization/i }).click();
    await expect(page).toHaveURL(/dashboard/, { timeout: 15_000 });
  });

  test('newly created organization appears in the org switcher', async ({ page }) => {
    const orgName = `Switcher Test ${Date.now()}`;
    await page.goto('/organizations/create');
    await page.getByLabel(/organization name/i).fill(orgName);
    await page.getByRole('button', { name: /create organization/i }).click();
    await expect(page).toHaveURL(/dashboard/, { timeout: 15_000 });
    // After creation, the sidebar shows the new org as the current one
    await expect(orgTrigger(page, new RegExp(orgName, 'i'))).toBeVisible();
  });
});

test.describe('Organization switcher', () => {
  test.use({ storageState: 'storage/e2e/alice.json' });

  // Restore Alice to Acme Corp since org creation tests switch her away
  test.beforeAll(() => resetAliceOrg());

  test('sidebar shows current organization name', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(orgTrigger(page, /Acme Corp/i)).toBeVisible();
  });

  test('org switcher dropdown lists available organizations', async ({ page }) => {
    await page.goto('/dashboard');
    await orgTrigger(page, /Acme Corp/i).click();
    await page.waitForTimeout(300);
    await expect(page.getByText(/New Organization/i).first()).toBeVisible();
  });

  test('can navigate to New Organization from the switcher', async ({ page }) => {
    await page.goto('/dashboard');
    await orgTrigger(page, /Acme Corp/i).click();
    await page.waitForTimeout(300);
    await page.getByText(/New Organization/i).first().click();
    await expect(page).toHaveURL(/organizations\/create/);
  });
});

test.describe('Organization access control', () => {
  test('organization create page requires authentication', async ({ page }) => {
    await page.goto('/organizations/create');
    await expect(page).toHaveURL(/login/);
  });

  test('member sees their own organization in the switcher', async ({ page }) => {
    await loginAs(page, BOB);
    await page.goto('/dashboard');
    await expect(orgTrigger(page, /Acme Corp/i)).toBeVisible();
  });
});
