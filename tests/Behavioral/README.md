# Behavioral Tests — BDD / Gherkin

Feature files describe the application's behavior in plain language using the
[Gherkin syntax](https://cucumber.io/docs/gherkin/reference/).

## Files

| Feature | Scenarios |
|---------|-----------|
| `account-management.feature` | Creating accounts, viewing list, validation, access control |
| `transaction-recording.feature` | Credit/debit transactions, balance calculation, validation |
| `authentication.feature` | Login, registration, logout, auth guards |

## Running BDD tests

### Option A — Pest's describe/it as BDD (current)

The PHP Pest tests in `tests/Functional/` and `tests/Integration/` implement
the scenarios described in these feature files. The feature files serve as
living documentation and specification.

### Option B — Cucumber PHP

Install [Behat](https://behat.org/) (PHP Cucumber implementation):

```bash
composer require --dev behat/behat behat/mink behat/mink-extension
vendor/bin/behat --init
vendor/bin/behat
```

Then implement step definitions in `features/bootstrap/FeatureContext.php`.

### Option C — Playwright + Cucumber (JS)

```bash
npm install --save-dev @cucumber/cucumber playwright
```

Write step definitions in `tests/E2e/steps/` and map them to the `.feature` files.

## Mapping to PHP tests

| Feature scenario | PHP test location |
|-----------------|-------------------|
| Account creation | `tests/Functional/Pages/AccountPagesTest.php` |
| Account access control | `tests/Integration/Policies/AccountPolicyTest.php` |
| Transaction recording | `tests/Functional/Pages/TransactionPagesTest.php` |
| Balance calculation | `tests/Integration/Models/AccountBalanceTest.php` |
| Login/logout | `tests/Functional/Auth/AuthenticationTest.php` |
| Auth guards | `tests/Smoke/ApplicationSmokeTest.php` |
