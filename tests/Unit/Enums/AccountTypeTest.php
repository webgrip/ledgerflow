<?php

declare(strict_types=1);

use App\Enums\AccountType;

describe('AccountType enum', function () {

    it('has the correct backing values', function () {
        expect(AccountType::Asset->value)->toBe('asset')
            ->and(AccountType::Liability->value)->toBe('liability')
            ->and(AccountType::Equity->value)->toBe('equity')
            ->and(AccountType::Revenue->value)->toBe('revenue')
            ->and(AccountType::Expense->value)->toBe('expense');
    });

    it('has exactly five cases', function () {
        expect(AccountType::cases())->toHaveCount(5);
    });

    it('returns a human-readable label for each case', function (AccountType $type, string $expected) {
        expect($type->label())->toBe($expected);
    })->with([
        'asset' => [AccountType::Asset,     'Asset'],
        'liability' => [AccountType::Liability, 'Liability'],
        'equity' => [AccountType::Equity,    'Equity'],
        'revenue' => [AccountType::Revenue,   'Revenue'],
        'expense' => [AccountType::Expense,   'Expense'],
    ]);

    it('can be instantiated from a valid backing value', function () {
        expect(AccountType::from('asset'))->toBe(AccountType::Asset)
            ->and(AccountType::from('revenue'))->toBe(AccountType::Revenue);
    });

    it('returns null for an invalid value via tryFrom', function () {
        expect(AccountType::tryFrom('unknown'))->toBeNull()
            ->and(AccountType::tryFrom(''))->toBeNull();
    });
});
