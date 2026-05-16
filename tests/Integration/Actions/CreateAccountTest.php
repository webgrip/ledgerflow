<?php

declare(strict_types=1);

use App\Actions\CreateAccount;
use App\Actions\CreateOrganization;
use App\Enums\AccountType;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('CreateAccount action', function () {

    it('creates an account belonging to the organization', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');

        $account = app(CreateAccount::class)->handle($org, 'Main Checking', AccountType::Asset);

        expect($account)->toBeInstanceOf(Account::class)
            ->and($account->organization_id)->toBe($org->id)
            ->and($account->name)->toBe('Main Checking')
            ->and($account->type)->toBe(AccountType::Asset);
    });

    it('defaults to USD currency when none is specified', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');

        $account = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

        expect($account->currency)->toBe('USD');
    });

    it('accepts a custom currency', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');

        $account = app(CreateAccount::class)->handle($org, 'Euro Account', AccountType::Asset, 'EUR');

        expect($account->currency)->toBe('EUR');
    });

    it('accepts an optional description', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');

        $account = app(CreateAccount::class)->handle($org, 'Savings', AccountType::Asset, 'USD', 'Emergency fund');

        expect($account->description)->toBe('Emergency fund');
    });

    it('stores null description when none provided', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');

        $account = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

        expect($account->description)->toBeNull();
    });

    it('persists the account to the database', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');

        app(CreateAccount::class)->handle($org, 'Petty Cash', AccountType::Expense);

        $this->assertDatabaseHas('accounts', [
            'name' => 'Petty Cash',
            'type' => 'expense',
            'organization_id' => $org->id,
        ]);
    });

    it('can create all five account types', function (AccountType $type) {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');

        $account = app(CreateAccount::class)->handle($org, 'Test', $type);

        expect($account->type)->toBe($type);
    })->with(AccountType::cases());
});
