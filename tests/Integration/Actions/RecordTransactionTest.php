<?php

declare(strict_types=1);

use App\Actions\RecordTransaction;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('RecordTransaction action', function () {

    it('creates a transaction on the given account', function () {
        $account = Account::factory()->create();

        $tx = app(RecordTransaction::class)->handle(
            account: $account,
            type: TransactionType::Credit,
            amountMinorUnits: 10000,
            description: 'Test deposit',
        );

        expect($tx)->toBeInstanceOf(Transaction::class)
            ->and($tx->account_id)->toBe($account->id)
            ->and($tx->type)->toBe(TransactionType::Credit)
            ->and($tx->amount_minor_units)->toBe(10000)
            ->and($tx->description)->toBe('Test deposit');
    });

    it('stores amount in minor units exactly as provided', function () {
        $account = Account::factory()->create();

        // $1,234.56 = 123456 minor units
        $tx = app(RecordTransaction::class)->handle($account, TransactionType::Debit, 123456, 'Payment');

        expect($tx->amount_minor_units)->toBe(123456);
    });

    it('defaults transacted_at to now when not provided', function () {
        $account = Account::factory()->create();
        $before = CarbonImmutable::now()->subSecond();

        $tx = app(RecordTransaction::class)->handle($account, TransactionType::Credit, 100, 'Now');

        expect($tx->transacted_at)->toBeGreaterThanOrEqual($before);
    });

    it('uses the provided transacted_at date', function () {
        $account = Account::factory()->create();
        $date = CarbonImmutable::parse('2024-01-15');

        $tx = app(RecordTransaction::class)->handle(
            account: $account,
            type: TransactionType::Credit,
            amountMinorUnits: 5000,
            description: 'Historical entry',
            transactedAt: $date,
        );

        expect($tx->transacted_at->toDateString())->toBe('2024-01-15');
    });

    it('persists the transaction to the database', function () {
        $account = Account::factory()->create();

        app(RecordTransaction::class)->handle($account, TransactionType::Credit, 500, 'Persisted');

        $this->assertDatabaseHas('transactions', [
            'account_id' => $account->id,
            'description' => 'Persisted',
            'amount_minor_units' => 500,
        ]);
    });

    it('can record both debit and credit transactions', function (TransactionType $type) {
        $account = Account::factory()->create();

        $tx = app(RecordTransaction::class)->handle($account, $type, 1000, 'Test');

        expect($tx->type)->toBe($type);
    })->with(TransactionType::cases());
});
