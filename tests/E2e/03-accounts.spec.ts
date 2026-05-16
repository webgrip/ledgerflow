/**
 * 03 — Account management flows
 */
import { execSync } from 'child_process';
import { test, expect } from '@playwright/test';
import { loginAs, ALICE, CAROL, createAccount } from './helpers.js';

// Selector for page-level XL headings (avoids sidebar user-name heading which is text-sm)
const PAGE_HEADING = '[data-flux-heading].text-2xl';

// Reset Alice's org to Acme Corp (ID 1) if org-creation tests polluted it
function resetAliceOrg() {
  execSync('vendor/bin/sail artisan e2e:reset-org', { cwd: process.cwd(), stdio: 'ignore' });
}

// ── Accounts index ─────────────────────────────────────────────────────────
test.describe('Accounts index', () => {
  test.use({ storageState: 'storage/e2e/alice.json' });

  test.beforeAll(async ({ browser }) => {
    // Restore Alice's org to Acme Corp in case org-creation tests polluted it
    resetAliceOrg();
    // Ensure at least one account exists so the table renders
    const ctx = await browser.newContext({ storageState: 'storage/e2e/alice.json' });
    const page = await ctx.newPage();
    await createAccount(page, `Index Seed ${Date.now()}`, 'asset');
    await ctx.close();
  });

  test('accounts page is accessible to authenticated users', async ({ page }) => {
    await page.goto('/accounts');
    await expect(page).not.toHaveURL(/login/);
    await expect(page.locator(PAGE_HEADING).first()).toBeVisible();
  });

  test('accounts page has a "New Account" button', async ({ page }) => {
    await page.goto('/accounts');
    const btn = page.getByRole('link', { name: /new account/i });
    await expect(btn).toBeVisible();
    await expect(btn).toHaveAttribute('href', /accounts\/create/);
  });

  test('accounts list shows table columns', async ({ page }) => {
    await page.goto('/accounts');
    await expect(page.getByText('Name').first()).toBeVisible();
    await expect(page.getByText('Type').first()).toBeVisible();
    await expect(page.getByText('Balance').first()).toBeVisible();
  });

  test('clicking an account row navigates to the account detail page', async ({ page }) => {
    await page.goto('/accounts');
    const firstRow = page.locator('table tbody tr').first();
    await expect(firstRow).toBeVisible();
    await firstRow.click();
    await expect(page).toHaveURL(/accounts\/\d+/);
  });
});

// ── Account creation ────────────────────────────────────────────────────────
test.describe('Account creation', () => {
  test.use({ storageState: 'storage/e2e/alice.json' });

  test.beforeAll(() => resetAliceOrg());

  test('create account page is accessible', async ({ page }) => {
    await page.goto('/accounts/create');
    await expect(page.locator(PAGE_HEADING).filter({ hasText: /new account/i }).first()).toBeVisible();
  });

  test('can create an asset account', async ({ page }) => {
    const name = `Asset ${Date.now()}`;
    await createAccount(page, name, 'asset');
    await expect(page.locator(PAGE_HEADING).first()).toContainText(name);
    await expect(page.getByText(/asset/i).first()).toBeVisible();
  });

  test('can create a liability account', async ({ page }) => {
    const name = `Liability ${Date.now()}`;
    await createAccount(page, name, 'liability');
    await expect(page.locator(PAGE_HEADING).first()).toContainText(name);
    await expect(page.getByText(/liability/i).first()).toBeVisible();
  });

  test('can create an equity account', async ({ page }) => {
    const name = `Equity ${Date.now()}`;
    await createAccount(page, name, 'equity');
    await expect(page.locator(PAGE_HEADING).first()).toContainText(name);
  });

  test('can create a revenue account', async ({ page }) => {
    const name = `Revenue ${Date.now()}`;
    await createAccount(page, name, 'revenue');
    await expect(page.locator(PAGE_HEADING).first()).toContainText(name);
  });

  test('can create an expense account', async ({ page }) => {
    const name = `Expense ${Date.now()}`;
    await createAccount(page, name, 'expense');
    await expect(page.locator(PAGE_HEADING).first()).toContainText(name);
  });

  test('empty name stays on create page (HTML5 required blocks submit)', async ({ page }) => {
    await page.goto('/accounts/create');
    await page.getByRole('button', { name: /create account/i }).click();
    await page.waitForTimeout(600);
    // HTML5 `required` on flux:input blocks native form submission; no navigation occurs
    await expect(page).toHaveURL(/accounts\/create/);
  });

  test('missing account type stays on create page (HTML5 required blocks submit)', async ({ page }) => {
    await page.goto('/accounts/create');
    await page.getByLabel(/account name/i).fill('No Type Account');
    await page.getByRole('button', { name: /create account/i }).click();
    await page.waitForTimeout(600);
    await expect(page).toHaveURL(/accounts\/create/);
  });

  test('cancel button returns to accounts list', async ({ page }) => {
    await page.goto('/accounts/create');
    await page.getByRole('link', { name: 'Cancel', exact: true }).click();
    await expect(page).toHaveURL(/accounts$/);
  });
});

// ── Account detail page ─────────────────────────────────────────────────────
test.describe('Account detail page', () => {
  test.use({ storageState: 'storage/e2e/alice.json' });

  test.beforeAll(() => resetAliceOrg());

  test('account detail page shows balance and account type', async ({ page }) => {
    const url = await createAccount(page, `Detail ${Date.now()}`, 'asset');
    const accountId = url.match(/accounts\/(\d+)/)?.[1];

    await page.goto(`/accounts/${accountId}`);
    await expect(page.getByText(/asset/i).first()).toBeVisible();
    await expect(page.getByText(/balance/i).first()).toBeVisible();
    await expect(page.getByText(/0\.00/)).toBeVisible();
  });

  test('account detail page has "Record Transaction" button', async ({ page }) => {
    const url = await createAccount(page, `RecordBtn ${Date.now()}`, 'asset');
    const accountId = url.match(/accounts\/(\d+)/)?.[1];

    await page.goto(`/accounts/${accountId}`);
    const btn = page.getByRole('link', { name: /record transaction/i });
    await expect(btn).toBeVisible();
    await expect(btn).toHaveAttribute('href', new RegExp(`accounts/${accountId}/transactions/create`));
  });

  test('account detail page shows "No transactions yet" for a fresh account', async ({ page }) => {
    const url = await createAccount(page, `Empty ${Date.now()}`, 'asset');
    const accountId = url.match(/accounts\/(\d+)/)?.[1];

    await page.goto(`/accounts/${accountId}`);
    await expect(page.getByText(/no transactions yet/i)).toBeVisible();
  });
});

// ── Account access control ──────────────────────────────────────────────────
test.describe('Account access control', () => {
  test('unauthenticated user cannot view accounts list', async ({ page }) => {
    await page.goto('/accounts');
    await expect(page).toHaveURL(/login/);
  });

  test('unauthenticated user cannot access account create page', async ({ page }) => {
    await page.goto('/accounts/create');
    await expect(page).toHaveURL(/login/);
  });

  test("carol cannot access alice's accounts", async ({ page }) => {
    resetAliceOrg();
    // Create account as Alice
    const browser = page.context().browser()!;
    const aliceCtx = await browser.newContext({ storageState: 'storage/e2e/alice.json' });
    const alicePage = await aliceCtx.newPage();
    const accountUrl = await createAccount(alicePage, `AlicePrivate ${Date.now()}`, 'asset');
    const accountId = accountUrl.match(/accounts\/(\d+)/)?.[1];
    await aliceCtx.close();

    // Try as Carol
    const carolCtx = await browser.newContext({ storageState: 'storage/e2e/carol.json' });
    const carolPage = await carolCtx.newPage();
    await carolPage.goto(`/accounts/${accountId}`);

    const body = await carolPage.content();
    const isForbidden =
      carolPage.url().includes('403') ||
      body.includes('403') ||
      body.includes('Forbidden') ||
      carolPage.url().includes('dashboard');
    await carolCtx.close();

    expect(isForbidden).toBe(true);
  });
});
