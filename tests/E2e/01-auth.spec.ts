/**
 * 01 — Authentication flows
 *
 * Covers: login success/failure, registration, logout, auth guards, password validation.
 */
import { test, expect } from '@playwright/test';
import { ALICE, loginAs, logout } from './helpers.js';

// ─── Guest-visible pages ──────────────────────────────────────────────────────

test.describe('Guest pages', () => {
  test('home page is accessible', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveURL('/');
    await expect(page).not.toHaveURL(/login/);
  });

  test('login page renders email + password fields', async ({ page }) => {
    await page.goto('/login');
    await expect(page.getByRole('textbox', { name: /email/i })).toBeVisible();
    await expect(page.getByRole('textbox', { name: /password/i })).toBeVisible();
    // Flux button — locate by data-test or type=submit
    await expect(page.locator('[data-test="login-button"]')).toBeVisible();
  });

  test('register page renders name, email and password fields', async ({ page }) => {
    await page.goto('/register');
    await expect(page.getByRole('textbox', { name: /name/i })).toBeVisible();
    await expect(page.getByRole('textbox', { name: /email/i })).toBeVisible();
    await expect(page.locator('[data-test="register-user-button"]')).toBeVisible();
  });
});

// ─── Auth guards ─────────────────────────────────────────────────────────────

test.describe('Auth guards', () => {
  test('GET /dashboard redirects guests to /login', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/login/);
  });

  test('GET /accounts redirects guests to /login', async ({ page }) => {
    await page.goto('/accounts');
    await expect(page).toHaveURL(/login/);
  });

  test('GET /accounts/create redirects guests to /login', async ({ page }) => {
    await page.goto('/accounts/create');
    await expect(page).toHaveURL(/login/);
  });

  test('GET /organizations/create redirects guests to /login', async ({ page }) => {
    await page.goto('/organizations/create');
    await expect(page).toHaveURL(/login/);
  });

  test('GET /dev is publicly accessible without auth', async ({ page }) => {
    await page.goto('/dev');
    await expect(page).toHaveURL('/dev');
    await expect(page).not.toHaveURL(/login/);
  });

  test('GET /up health check returns 200', async ({ page }) => {
    const response = await page.goto('/up');
    expect(response?.status()).toBe(200);
  });
});

// ─── Login ────────────────────────────────────────────────────────────────────

test.describe('Login', () => {
  test('valid credentials redirect to dashboard', async ({ page }) => {
    await loginAs(page, ALICE);
    await expect(page).toHaveURL(/dashboard/);
  });

  test('wrong password shows credential error and stays on /login', async ({ page }) => {
    await page.goto('/login');
    await page.getByRole('textbox', { name: /email/i }).fill(ALICE.email);
    await page.getByRole('textbox', { name: /password/i }).fill('wrongpassword');
    await page.locator('[data-test="login-button"]').click();

    await expect(page).toHaveURL(/login/);
    // Laravel Fortify returns "These credentials do not match our records."
    await expect(page.getByText(/credentials/i)).toBeVisible();
  });

  test('non-existent email shows credential error', async ({ page }) => {
    await page.goto('/login');
    await page.getByRole('textbox', { name: /email/i }).fill('nobody@nowhere.com');
    await page.getByRole('textbox', { name: /password/i }).fill('password');
    await page.locator('[data-test="login-button"]').click();

    await expect(page).toHaveURL(/login/);
    await expect(page.getByText(/credentials/i)).toBeVisible();
  });

  test('empty form shows validation errors', async ({ page }) => {
    await page.goto('/login');
    await page.locator('[data-test="login-button"]').click();

    await expect(page).toHaveURL(/login/);
  });
});

// ─── Logout ───────────────────────────────────────────────────────────────────

test.describe('Logout', () => {
  test('logout clears session and visiting /dashboard redirects to /login', async ({ page }) => {
    await loginAs(page, ALICE);
    await expect(page).toHaveURL(/dashboard/);

    // The logout is inside a Flux dropdown triggered by the sidebar profile button
    await page.locator('[data-test="sidebar-menu-button"]').click();
    await page.waitForTimeout(300);

    // Now the logout form should be visible — submit it via data-test
    await page.locator('[data-test="logout-button"]').first().click();

    await expect(page).toHaveURL(/login|\//);

    // Verify session is gone
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/login/);
  });
});

// ─── Registration ─────────────────────────────────────────────────────────────

test.describe('Registration', () => {
  test('new user can register and is redirected away from /register', async ({ page }) => {
    const uniqueEmail = `e2e-${Date.now()}@example.com`;

    await page.goto('/register');
    await page.getByRole('textbox', { name: /^name/i }).fill('E2E User');
    await page.getByRole('textbox', { name: /email/i }).fill(uniqueEmail);

    const passwordFields = page.getByRole('textbox', { name: /password/i });
    await passwordFields.first().fill('Password123!');
    await passwordFields.last().fill('Password123!');

    await page.locator('[data-test="register-user-button"]').click();

    await expect(page).not.toHaveURL(/register/, { timeout: 15_000 });
  });

  test('duplicate email shows validation error', async ({ page }) => {
    await page.goto('/register');
    await page.getByRole('textbox', { name: /^name/i }).fill('Dup User');
    await page.getByRole('textbox', { name: /email/i }).fill(ALICE.email); // taken

    const passwordFields = page.getByRole('textbox', { name: /password/i });
    await passwordFields.first().fill('Password123!');
    await passwordFields.last().fill('Password123!');

    await page.locator('[data-test="register-user-button"]').click();

    await expect(page).toHaveURL(/register/);
    await expect(page.getByText(/already been taken/i)).toBeVisible();
  });

  test('password confirmation mismatch shows error', async ({ page }) => {
    await page.goto('/register');
    await page.getByRole('textbox', { name: /^name/i }).fill('Test User');
    await page.getByRole('textbox', { name: /email/i }).fill(`mismatch-${Date.now()}@example.com`);

    const passwordFields = page.getByRole('textbox', { name: /password/i });
    await passwordFields.first().fill('Password123!');
    await passwordFields.last().fill('DifferentPass!');

    await page.locator('[data-test="register-user-button"]').click();

    await expect(page).toHaveURL(/register/);
    await expect(page.getByText(/confirmation/i)).toBeVisible();
  });
});
