<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Contract tests for Transaction — enforcing the invariants consumers rely on.
 */
describe('Transaction data contract', function () {

    it('always has account_id, type, amount_minor_units, description, and transacted_at', function () {
        $tx = Transaction::factory()->create();

        expect($tx->account_id)->not->toBeNull()
            ->and($tx->type)->toBeInstanceOf(TransactionType::class)
            ->and($tx->amount_minor_units)->toBeInt()
            ->and($tx->description)->toBeString()->not->toBeEmpty()
            ->and($tx->transacted_at)->toBeInstanceOf(CarbonImmutable::class);
    });

    it('amount_minor_units is always a positive integer', function () {
        Transaction::factory()->count(20)->create();

        Transaction::all()->each(function (Transaction $tx) {
            expect($tx->amount_minor_units)->toBeInt()->toBeGreaterThan(0);
        });
    });

    it('type is always one of the two defined TransactionType cases', function () {
        Transaction::factory()->count(20)->create();

        $validValues = collect(TransactionType::cases())->pluck('value')->all();

        Transaction::all()->each(function (Transaction $tx) use ($validValues) {
            expect($tx->getRawOriginal('type'))->toBeIn($validValues);
        });
    });

    it('transacted_at is always cast to CarbonImmutable', function () {
        $tx = Transaction::factory()->create();

        expect($tx->transacted_at)->toBeInstanceOf(CarbonImmutable::class);
    });

    it('always belongs to an account', function () {
        $tx = Transaction::factory()->create();
        $tx->load('account');

        expect($tx->account)->toBeInstanceOf(Account::class);
    });

    it('factory credit() state produces a credit transaction', function () {
        $tx = Transaction::factory()->credit()->create();

        expect($tx->type)->toBe(TransactionType::Credit);
    });

    it('factory debit() state produces a debit transaction', function () {
        $tx = Transaction::factory()->debit()->create();

        expect($tx->type)->toBe(TransactionType::Debit);
    });

    it('has timestamps (created_at, updated_at)', function () {
        $tx = Transaction::factory()->create();

        expect($tx->created_at)->not->toBeNull()
            ->and($tx->updated_at)->not->toBeNull();
    });
});
