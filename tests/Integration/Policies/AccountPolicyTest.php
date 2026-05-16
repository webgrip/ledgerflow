<?php

declare(strict_types=1);

use App\Actions\CreateAccount;
use App\Actions\CreateOrganization;
use App\Enums\AccountType;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

describe('AccountPolicy', function () {

    describe('viewAny', function () {
        it('allows a user with a current organization to list accounts', function () {
            $user = User::factory()->create();
            app(CreateOrganization::class)->handle($user, 'Acme');

            expect(Gate::forUser($user)->allows('viewAny', Account::class))->toBeTrue();
        });

        it('denies a user without a current organization from listing accounts', function () {
            $user = User::factory()->create(); // no org set

            expect(Gate::forUser($user)->denies('viewAny', Account::class))->toBeTrue();
        });
    });

    describe('view', function () {
        it('allows an org member to view an account', function () {
            $user = User::factory()->create();
            $org = app(CreateOrganization::class)->handle($user, 'Acme');
            $account = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

            expect(Gate::forUser($user)->allows('view', $account))->toBeTrue();
        });

        it('denies a non-member from viewing an account', function () {
            $outsider = User::factory()->create();
            $account = Account::factory()->create();

            expect(Gate::forUser($outsider)->denies('view', $account))->toBeTrue();
        });

        it('denies cross-org account viewing', function () {
            $userA = User::factory()->create();
            $orgA = app(CreateOrganization::class)->handle($userA, 'Org A');
            $acctA = app(CreateAccount::class)->handle($orgA, 'Checking', AccountType::Asset);

            $userB = User::factory()->create();
            app(CreateOrganization::class)->handle($userB, 'Org B'); // sets current_org for B

            expect(Gate::forUser($userB)->denies('view', $acctA))->toBeTrue();
        });
    });

    describe('create', function () {
        it('allows a user with an active organization to create an account', function () {
            $user = User::factory()->create();
            app(CreateOrganization::class)->handle($user, 'Acme');

            expect(Gate::forUser($user)->allows('create', Account::class))->toBeTrue();
        });

        it('denies creating an account when user has no current organization', function () {
            $user = User::factory()->create(); // no org

            expect(Gate::forUser($user)->denies('create', Account::class))->toBeTrue();
        });
    });

    describe('update', function () {
        it('allows org members to update an account', function () {
            $user = User::factory()->create();
            $org = app(CreateOrganization::class)->handle($user, 'Acme');
            $acct = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

            expect(Gate::forUser($user)->allows('update', $acct))->toBeTrue();
        });

        it('denies non-members from updating an account', function () {
            $outsider = User::factory()->create();
            $account = Account::factory()->create();

            expect(Gate::forUser($outsider)->denies('update', $account))->toBeTrue();
        });
    });

    describe('delete', function () {
        it('allows org members to delete an account', function () {
            $user = User::factory()->create();
            $org = app(CreateOrganization::class)->handle($user, 'Acme');
            $acct = app(CreateAccount::class)->handle($org, 'Savings', AccountType::Asset);

            expect(Gate::forUser($user)->allows('delete', $acct))->toBeTrue();
        });

        it('denies non-members from deleting an account', function () {
            $outsider = User::factory()->create();
            $account = Account::factory()->create();

            expect(Gate::forUser($outsider)->denies('delete', $account))->toBeTrue();
        });
    });
});
