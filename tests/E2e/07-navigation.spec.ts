/**
 * 07 — Navigation and layout flows
 */
import { test, expect } from '@playwright/test';

const PAGE_HEADING = '[data-flux-heading].text-2xl';

test.describe('Navigation — authenticated', () => {
  test.use({ storageState: 'storage/e2e/alice.json' });

  test('sidebar brand / logo is visible', async ({ page }) => {
    await page.goto('/dashboard');
    const logo = page.getByRole('link', { name: /Laravel Starter Kit|LedgerFlow|starter kit/i }).first();
    await expect(logo).toBeVisible();
  });

  test('sidebar shows main nav links', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page.getByRole('link', { name: /accounts/i }).first()).toBeVisible();
  });

  test('sidebar is present on accounts page', async ({ page }) => {
    await page.goto('/accounts');
    await expect(page.locator('[data-flux-sidebar], nav, [role="navigation"]').first()).toBeVisible();
  });

  test('can navigate to /accounts via sidebar link', async ({ page }) => {
    await page.goto('/dashboard');
    await page.getByRole('link', { name: /accounts/i }).first().click();
    await expect(page).toHaveURL(/accounts/);
  });

  test('/dashboard is accessible when authenticated', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/dashboard/);
  });
});

test.describe('Navigation — unauthenticated redirects', () => {
  test('/ shows the welcome/home page (public)', async ({ page }) => {
    await page.goto('/');
    // The root route shows a public welcome page, not a login redirect
    await expect(page).not.toHaveURL(/login/);
  });

  test('/dashboard redirects unauthenticated user to /login', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/login/);
  });

  test('/accounts redirects unauthenticated user to /login', async ({ page }) => {
    await page.goto('/accounts');
    await expect(page).toHaveURL(/login/);
  });

  test('/accounts/create redirects unauthenticated user to /login', async ({ page }) => {
    await page.goto('/accounts/create');
    await expect(page).toHaveURL(/login/);
  });

  test('/dev is publicly accessible', async ({ page }) => {
    await page.goto('/dev');
    await expect(page).not.toHaveURL(/login/);
    await expect(page).toHaveURL('/dev');
  });

  test('/login is accessible to guests', async ({ page }) => {
    await page.goto('/login');
    await expect(page).toHaveURL(/login/);
    await expect(page.getByRole('textbox', { name: /email/i })).toBeVisible();
  });

  test('/register is accessible to guests', async ({ page }) => {
    await page.goto('/register');
    await expect(page).toHaveURL(/register/);
  });
});

test.describe('Navigation — page headings', () => {
  test.use({ storageState: 'storage/e2e/alice.json' });

  test('accounts list page shows XL heading', async ({ page }) => {
    await page.goto('/accounts');
    await expect(page.locator(PAGE_HEADING).first()).toBeVisible();
    await expect(page.locator(PAGE_HEADING).first()).toContainText(/accounts/i);
  });

  test('account create page shows "New Account" heading', async ({ page }) => {
    await page.goto('/accounts/create');
    await expect(page.locator(PAGE_HEADING).first()).toBeVisible();
    await expect(page.locator(PAGE_HEADING).first()).toContainText(/new account/i);
  });
});
