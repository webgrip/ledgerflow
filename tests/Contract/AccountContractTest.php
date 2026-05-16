<?php

declare(strict_types=1);

use App\Actions\CreateAccount;
use App\Actions\CreateOrganization;
use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Contract tests assert the _shape_ of domain objects — their required fields,
 * types, and invariants — not their business logic. These tests document and
 * enforce the data contract consumers of these models can rely on.
 */
describe('Account data contract', function () {

    it('always has organization_id, name, type, and currency', function () {
        $account = Account::factory()->create();

        expect($account->organization_id)->not->toBeNull()
            ->and($account->name)->toBeString()->not->toBeEmpty()
            ->and($account->type)->toBeInstanceOf(AccountType::class)
            ->and($account->currency)->toBeString()->toHaveLength(3);
    });

    it('type is always one of the defined AccountType enum cases', function () {
        Account::factory()->count(10)->create();

        Account::all()->each(function (Account $account) {
            expect(AccountType::tryFrom($account->getRawOriginal('type')))->not->toBeNull();
        });
    });

    it('currency is always exactly 3 uppercase-style characters', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');
        $account = app(CreateAccount::class)->handle($org, 'FX Account', AccountType::Asset, 'EUR');

        expect(strlen($account->currency))->toBe(3);
    });

    it('factory-created accounts always have a valid type', function () {
        $accounts = Account::factory()->count(20)->create();

        foreach ($accounts as $account) {
            expect(AccountType::tryFrom($account->getRawOriginal('type')))->not->toBeNull();
        }
    });

    it('description is nullable', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');

        $withDesc = app(CreateAccount::class)->handle($org, 'With Desc', AccountType::Asset, 'USD', 'A description');
        $withoutDesc = app(CreateAccount::class)->handle($org, 'No Desc', AccountType::Asset);

        expect($withDesc->description)->toBeString()
            ->and($withoutDesc->description)->toBeNull();
    });

    it('always belongs to an organization', function () {
        $account = Account::factory()->create();
        $account->load('organization');

        expect($account->organization)->toBeInstanceOf(Organization::class);
    });

    it('has timestamps (created_at, updated_at)', function () {
        $account = Account::factory()->create();

        expect($account->created_at)->not->toBeNull()
            ->and($account->updated_at)->not->toBeNull();
    });

    it('balance() always returns an integer', function () {
        $account = Account::factory()->create();

        expect($account->balance())->toBeInt();
    });
});
