/**
 * Shared helpers for E2E tests.
 */
import { Page, expect } from '@playwright/test';

export const ALICE = { email: 'alice@demo.test', password: 'password', name: 'Alice' };
export const BOB   = { email: 'bob@demo.test',   password: 'password', name: 'Bob' };
export const CAROL = { email: 'carol@demo.test',  password: 'password', name: 'Carol' };

/**
 * Log in as a demo user and wait for the dashboard.
 */
export async function loginAs(
  page: Page,
  user: { email: string; password: string } = ALICE,
): Promise<void> {
  await page.goto('/login');
  await page.getByRole('textbox', { name: /email/i }).fill(user.email);
  await page.getByRole('textbox', { name: /password/i }).fill(user.password);
  // Flux button may have no accessible text — use data-test or type=submit
  await page.locator('[data-test="login-button"], button[type="submit"]').first().click();
  // Wait until we've left /login
  await expect(page).not.toHaveURL(/login/, { timeout: 15_000 });
}

/**
 * Log out via the sidebar user menu.
 */
export async function logout(page: Page): Promise<void> {
  await page.locator('[data-test="sidebar-menu-button"]').click();
  await page.waitForTimeout(300);
  await page.locator('[data-test="logout-button"]').first().click();
  await expect(page).toHaveURL(/login|\//);
}

/**
 * Create an account via the UI and return the new account URL.
 */
export async function createAccount(
  page: Page,
  name: string,
  type: 'asset' | 'liability' | 'equity' | 'revenue' | 'expense' = 'asset',
  currency = 'USD',
): Promise<string> {
  await page.goto('/accounts/create');
  await page.getByLabel(/account name/i).fill(name);
  await page.getByLabel(/account type/i).selectOption(type);
  await page.getByLabel(/currency/i).fill(currency);
  await page.getByRole('button', { name: /create account/i }).click();
  await expect(page).toHaveURL(/accounts\/\d+/, { timeout: 10_000 });
  return page.url();
}

/**
 * Record a transaction from the account show page.
 */
export async function recordTransaction(
  page: Page,
  accountId: number,
  type: 'credit' | 'debit',
  amount: string,
  description: string,
): Promise<void> {
  await page.goto(`/accounts/${accountId}/transactions/create`);
  await page.getByLabel(/type/i).selectOption(type);
  await page.getByLabel(/amount/i).fill(amount);
  await page.getByLabel(/description/i).fill(description);
  await page.getByRole('button', { name: /record/i }).click();
  await expect(page).toHaveURL(new RegExp(`accounts/${accountId}$`), { timeout: 10_000 });
}
