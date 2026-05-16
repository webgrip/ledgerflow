<?php

declare(strict_types=1);

use App\Actions\CreateOrganization;
use App\Actions\RecordTransaction;
use App\Enums\AccountType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Account::balance()', function () {

    it('returns zero for an account with no transactions', function () {
        $account = Account::factory()->create();

        expect($account->balance())->toBe(0);
    });

    it('returns the sum of credits for a credit-only account', function () {
        $account = Account::factory()->create();

        app(RecordTransaction::class)->handle($account, TransactionType::Credit, 10000, 'A');
        app(RecordTransaction::class)->handle($account, TransactionType::Credit, 5000, 'B');

        expect($account->balance())->toBe(15000);
    });

    it('subtracts debits from credits', function () {
        $account = Account::factory()->create();

        app(RecordTransaction::class)->handle($account, TransactionType::Credit, 20000, 'Deposit');
        app(RecordTransaction::class)->handle($account, TransactionType::Debit, 8000, 'Withdrawal');

        expect($account->balance())->toBe(12000);
    });

    it('can return a negative balance when debits exceed credits', function () {
        $account = Account::factory()->create();

        app(RecordTransaction::class)->handle($account, TransactionType::Debit, 5000, 'Overdraft');

        expect($account->balance())->toBe(-5000);
    });

    it('handles large amounts correctly without floating-point error', function () {
        $account = Account::factory()->create();

        // $999,999.99 credit  − $0.01 debit = $999,999.98
        app(RecordTransaction::class)->handle($account, TransactionType::Credit, 99999999, 'Big credit');
        app(RecordTransaction::class)->handle($account, TransactionType::Debit, 1, 'Tiny debit');

        expect($account->balance())->toBe(99999998);
    });

    it('isolates balances per account — transactions on one account do not affect another', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');

        $checking = Account::factory()->create(['organization_id' => $org->id, 'type' => AccountType::Asset]);
        $savings = Account::factory()->create(['organization_id' => $org->id, 'type' => AccountType::Asset]);

        app(RecordTransaction::class)->handle($checking, TransactionType::Credit, 10000, 'Checking credit');
        app(RecordTransaction::class)->handle($savings, TransactionType::Credit, 3000, 'Savings credit');

        expect($checking->balance())->toBe(10000)
            ->and($savings->balance())->toBe(3000);
    });

    it('recalculates correctly after new transactions are recorded', function () {
        $account = Account::factory()->create();
        app(RecordTransaction::class)->handle($account, TransactionType::Credit, 5000, 'First');

        expect($account->balance())->toBe(5000);

        app(RecordTransaction::class)->handle($account, TransactionType::Debit, 2000, 'Second');

        expect($account->balance())->toBe(3000);
    });
});
