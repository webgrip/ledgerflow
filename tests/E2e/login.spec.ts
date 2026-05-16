import { test, expect } from '@playwright/test';

/**
 * Authentication E2E tests.
 *
 * Prerequisites: seed demo data via `GET /dev` → "Seed Demo Data"
 * or ensure a user alice@demo.test / password exists.
 */

test.describe('Login flow', () => {
  test('login page is accessible to guests', async ({ page }) => {
    await page.goto('/login');
    await expect(page).toHaveTitle(/Log in/i);
    await expect(page.getByRole('textbox', { name: /email/i })).toBeVisible();
    await expect(page.getByRole('textbox', { name: /password/i })).toBeVisible();
  });

  test('valid credentials authenticate and redirect to dashboard', async ({ page }) => {
    await page.goto('/login');
    await page.getByRole('textbox', { name: /email/i }).fill('alice@demo.test');
    await page.getByRole('textbox', { name: /password/i }).fill('password');
    await page.getByRole('button', { name: /log in/i }).click();

    // Should land on dashboard, not still on /login
    await expect(page).not.toHaveURL(/login/);
    await expect(page.getByText(/dashboard/i).first()).toBeVisible();
  });

  test('invalid credentials show an error message', async ({ page }) => {
    await page.goto('/login');
    await page.getByRole('textbox', { name: /email/i }).fill('alice@demo.test');
    await page.getByRole('textbox', { name: /password/i }).fill('wrongpassword');
    await page.getByRole('button', { name: /log in/i }).click();

    // Should stay on login with an error
    await expect(page).toHaveURL(/login/);
    await expect(page.getByText(/credentials/i).first()).toBeVisible();
  });

  test('logout clears the session and redirects to home or login', async ({ page }) => {
    // Login first
    await page.goto('/login');
    await page.getByRole('textbox', { name: /email/i }).fill('alice@demo.test');
    await page.getByRole('textbox', { name: /password/i }).fill('password');
    await page.getByRole('button', { name: /log in/i }).click();
    await expect(page).not.toHaveURL(/login/);

    // Click logout (button text varies by theme)
    const logoutBtn = page.getByRole('button', { name: /log out/i });
    if (await logoutBtn.isVisible()) {
      await logoutBtn.click();
    } else {
      // May be in a dropdown
      await page.getByRole('button', { name: /alice/i }).click();
      await page.getByRole('menuitem', { name: /log out/i }).click();
    }

    await expect(page).toHaveURL(/login|^\//);
  });

  test('protected dashboard redirects guests to login', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/login/);
  });
});

test.describe('Registration flow', () => {
  test('registration page is accessible', async ({ page }) => {
    await page.goto('/register');
    await expect(page.getByRole('textbox', { name: /name/i })).toBeVisible();
  });

  test('new user can register', async ({ page }) => {
    const email = `e2e-${Date.now()}@example.com`;
    await page.goto('/register');
    await page.getByRole('textbox', { name: /name/i }).fill('E2E User');
    await page.getByRole('textbox', { name: /email/i }).fill(email);

    const passwords = page.getByRole('textbox', { name: /password/i });
    await passwords.first().fill('password123');
    await passwords.last().fill('password123');

    await page.getByRole('button', { name: /register/i }).click();
    await expect(page).not.toHaveURL(/register/);
  });
});
