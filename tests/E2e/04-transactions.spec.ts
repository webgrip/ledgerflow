/**
 * 04 — Transaction recording and balance flows
 */
import { execSync } from 'child_process';
import { test, expect } from '@playwright/test';
import { createAccount, recordTransaction } from './helpers.js';

function resetAliceOrg() {
  execSync('vendor/bin/sail artisan e2e:reset-org', { cwd: process.cwd(), stdio: 'ignore' });
}

test.describe('Record transaction — form', () => {
  test.use({ storageState: 'storage/e2e/alice.json' });
  test.beforeAll(() => resetAliceOrg());

  test('transaction create page is accessible from account detail', async ({ page }) => {
    const url = await createAccount(page, `Form ${Date.now()}`, 'asset');
    const accountId = url.match(/accounts\/(\d+)/)![1];

    await page.goto(`/accounts/${accountId}`);
    await page.getByRole('link', { name: /record transaction/i }).click();
    await expect(page).toHaveURL(new RegExp(`accounts/${accountId}/transactions/create`));
  });

  test('form has type, amount, description and date fields', async ({ page }) => {
    const url = await createAccount(page, `Fields ${Date.now()}`, 'asset');
    const accountId = url.match(/accounts\/(\d+)/)![1];

    await page.goto(`/accounts/${accountId}/transactions/create`);
    await expect(page.getByLabel(/type/i)).toBeVisible();
    await expect(page.getByLabel(/amount/i)).toBeVisible();
    await expect(page.getByLabel(/description/i)).toBeVisible();
    await expect(page.getByLabel(/date/i)).toBeVisible();
  });

  test('date field defaults to today', async ({ page }) => {
    const url = await createAccount(page, `DateField ${Date.now()}`, 'asset');
    const accountId = url.match(/accounts\/(\d+)/)![1];

    await page.goto(`/accounts/${accountId}/transactions/create`);
    const today = new Date().toISOString().split('T')[0];
    await expect(page.getByLabel(/date/i)).toHaveValue(today);
  });

  test('cancel button returns to account detail', async ({ page }) => {
    const url = await createAccount(page, `BackNav ${Date.now()}`, 'asset');
    const accountId = url.match(/accounts\/(\d+)/)![1];

    await page.goto(`/accounts/${accountId}/transactions/create`);
    // flux:button with :href renders as <a> not <button>
    await page.getByRole('link', { name: 'Cancel', exact: true }).click();
    await expect(page).toHaveURL(new RegExp(`accounts/${accountId}$`));
  });
});

test.describe('Record transaction — credit', () => {
  test.use({ storageState: 'storage/e2e/alice.json' });
  test.beforeAll(() => resetAliceOrg());

  test('can record a credit and is redirected to account detail', async ({ page }) => {
    const url = await createAccount(page, `Credit ${Date.now()}`, 'asset');
    const accountId = parseInt(url.match(/accounts\/(\d+)/)![1]);
    await recordTransaction(page, accountId, 'credit', '500.00', 'Initial deposit');
    await expect(page).toHaveURL(new RegExp(`accounts/${accountId}$`));
  });

  test('recorded credit appears in the transaction list', async ({ page }) => {
    const url = await createAccount(page, `CreditList ${Date.now()}`, 'asset');
    const accountId = parseInt(url.match(/accounts\/(\d+)/)![1]);
    const desc = `Deposit ${Date.now()}`;
    await recordTransaction(page, accountId, 'credit', '250.00', desc);
    await expect(page.getByText(desc)).toBeVisible();
    await expect(page.getByText('Credit').first()).toBeVisible();
  });

  test('credit increases the account balance', async ({ page }) => {
    const url = await createAccount(page, `CreditBal ${Date.now()}`, 'asset');
    const accountId = parseInt(url.match(/accounts\/(\d+)/)![1]);
    await recordTransaction(page, accountId, 'credit', '1000.00', 'Test credit');
    await expect(page.getByText('1,000.00').first()).toBeVisible();
  });

  test('amount is rendered with 2 decimal places', async ({ page }) => {
    const url = await createAccount(page, `Decimal ${Date.now()}`, 'asset');
    const accountId = parseInt(url.match(/accounts\/(\d+)/)![1]);
    await recordTransaction(page, accountId, 'credit', '123.45', 'Decimal test');
    await expect(page.getByText('123.45').first()).toBeVisible();
  });
});

test.describe('Record transaction — debit', () => {
  test.use({ storageState: 'storage/e2e/alice.json' });
  test.beforeAll(() => resetAliceOrg());

  test('can record a debit transaction', async ({ page }) => {
    const url = await createAccount(page, `Debit ${Date.now()}`, 'asset');
    const accountId = parseInt(url.match(/accounts\/(\d+)/)![1]);
    await recordTransaction(page, accountId, 'credit', '2000.00', 'Pre-fund');
    const desc = `Withdrawal ${Date.now()}`;
    await recordTransaction(page, accountId, 'debit', '300.00', desc);
    await expect(page.getByText(desc)).toBeVisible();
    await expect(page.getByText('Debit').first()).toBeVisible();
  });

  test('debit decreases the account balance', async ({ page }) => {
    const url = await createAccount(page, `DebitBal ${Date.now()}`, 'asset');
    const accountId = parseInt(url.match(/accounts\/(\d+)/)![1]);
    await recordTransaction(page, accountId, 'credit', '2000.00', 'Pre-fund');
    await recordTransaction(page, accountId, 'debit', '500.00', 'Withdrawal');
    await expect(page.getByText('1,500.00').first()).toBeVisible();
  });
});

test.describe('Record transaction — balance calculation', () => {
  test.use({ storageState: 'storage/e2e/alice.json' });
  test.beforeAll(() => resetAliceOrg());

  test('balance = sum of credits minus sum of debits', async ({ page }) => {
    const url = await createAccount(page, `BalCalc ${Date.now()}`, 'asset');
    const accountId = parseInt(url.match(/accounts\/(\d+)/)![1]);
    await recordTransaction(page, accountId, 'credit', '1000.00', 'Credit 1');
    await recordTransaction(page, accountId, 'credit', '500.00',  'Credit 2');
    await recordTransaction(page, accountId, 'debit',  '300.00',  'Debit 1');
    await page.goto(`/accounts/${accountId}`);
    await expect(page.getByText('1,200.00').first()).toBeVisible();
  });

  test('transaction list shows multiple transactions', async ({ page }) => {
    const url = await createAccount(page, `TxList ${Date.now()}`, 'asset');
    const accountId = parseInt(url.match(/accounts\/(\d+)/)![1]);
    await recordTransaction(page, accountId, 'credit', '100.00', 'First');
    await recordTransaction(page, accountId, 'credit', '200.00', 'Second');
    await page.goto(`/accounts/${accountId}`);
    const rows = page.locator('table tbody tr');
    await expect(rows).toHaveCount(2);
  });
});

test.describe('Record transaction — validation', () => {
  test.use({ storageState: 'storage/e2e/alice.json' });
  test.beforeAll(() => resetAliceOrg());

  test('zero amount stays on create page', async ({ page }) => {
    const url = await createAccount(page, `ValZero ${Date.now()}`, 'asset');
    const accountId = parseInt(url.match(/accounts\/(\d+)/)![1]);

    await page.goto(`/accounts/${accountId}/transactions/create`);
    await page.getByLabel(/amount/i).fill('0');
    await page.getByLabel(/description/i).fill('Test zero');
    await page.getByRole('button', { name: /record/i, exact: false }).click();
    await page.waitForTimeout(600);

    await expect(page).toHaveURL(new RegExp(`accounts/${accountId}/transactions/create`));
  });

  test('short description (< 2 chars) stays on create page', async ({ page }) => {
    const url = await createAccount(page, `ValDesc ${Date.now()}`, 'asset');
    const accountId = parseInt(url.match(/accounts\/(\d+)/)![1]);

    await page.goto(`/accounts/${accountId}/transactions/create`);
    await page.getByLabel(/amount/i).fill('100');
    await page.getByLabel(/description/i).fill('x'); // min:2 fails
    await page.getByRole('button', { name: /record/i, exact: false }).click();
    await page.waitForTimeout(600);

    await expect(page).toHaveURL(new RegExp(`accounts/${accountId}/transactions/create`));
  });
});

test.describe('Transaction access control', () => {
  test('unauthenticated user cannot access transaction create page', async ({ page }) => {
    await page.goto('/accounts/1/transactions/create');
    await expect(page).toHaveURL(/login/);
  });

  test("carol cannot create transactions on alice's accounts", async ({ page }) => {
    resetAliceOrg();
    const browser = page.context().browser()!;
    const aliceCtx = await browser.newContext({ storageState: 'storage/e2e/alice.json' });
    const alicePage = await aliceCtx.newPage();
    const url = await createAccount(alicePage, `AlicePrivateTx ${Date.now()}`, 'asset');
    const accountId = url.match(/accounts\/(\d+)/)![1];
    await aliceCtx.close();

    const carolCtx = await browser.newContext({ storageState: 'storage/e2e/carol.json' });
    const carolPage = await carolCtx.newPage();
    await carolPage.goto(`/accounts/${accountId}/transactions/create`);

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
