<?php

declare(strict_types=1);

use App\Enums\TransactionType;

describe('TransactionType enum', function () {

    it('has debit and credit as the only two cases', function () {
        expect(TransactionType::cases())->toHaveCount(2);
    });

    it('has the correct backing values', function () {
        expect(TransactionType::Debit->value)->toBe('debit')
            ->and(TransactionType::Credit->value)->toBe('credit');
    });

    it('can round-trip through from() and value', function () {
        foreach (TransactionType::cases() as $case) {
            expect(TransactionType::from($case->value))->toBe($case);
        }
    });

    it('returns null for an invalid value via tryFrom', function () {
        expect(TransactionType::tryFrom('transfer'))->toBeNull();
    });
});
