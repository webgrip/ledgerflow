<?php

declare(strict_types=1);

use App\Actions\CreateAccount;
use App\Actions\CreateOrganization;
use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('Accounts index page', function () {
    it('redirects guests to login', function () {
        $this->get(route('accounts.index'))->assertRedirect(route('login'));
    });

    it('returns 200 for an authenticated user with an organization', function () {
        $user = User::factory()->create();
        app(CreateOrganization::class)->handle($user, 'Acme');

        $this->actingAs($user)->get(route('accounts.index'))->assertOk();
    });

    it('shows accounts belonging to the current organization', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');
        $acct = app(CreateAccount::class)->handle($org, 'Main Checking', AccountType::Asset);

        $this->actingAs($user)->get(route('accounts.index'))
            ->assertOk()
            ->assertSee('Main Checking');
    });

    it('does not show accounts from other organizations', function () {
        $user = User::factory()->create();
        app(CreateOrganization::class)->handle($user, 'Acme');

        $other = Organization::factory()->create();
        $otherAccount = Account::factory()->create(['organization_id' => $other->id, 'name' => 'Other Account']);

        $this->actingAs($user)->get(route('accounts.index'))
            ->assertOk()
            ->assertDontSee('Other Account');
    });
});

describe('Accounts create page', function () {
    it('redirects guests to login', function () {
        $this->get(route('accounts.create'))->assertRedirect(route('login'));
    });

    it('returns 200 for an authenticated user with an organization', function () {
        $user = User::factory()->create();
        app(CreateOrganization::class)->handle($user, 'Acme');

        $this->actingAs($user)->get(route('accounts.create'))->assertOk();
    });

    it('creates an account and redirects to account show', function () {
        $user = User::factory()->create();
        app(CreateOrganization::class)->handle($user, 'Acme');

        Livewire::actingAs($user)
            ->test('pages::accounts.create')
            ->set('name', 'Operating Account')
            ->set('type', 'asset')
            ->set('currency', 'USD')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirectContains('accounts');

        expect(Account::where('name', 'Operating Account')->exists())->toBeTrue();
    });

    it('fails validation when name is missing', function () {
        $user = User::factory()->create();
        app(CreateOrganization::class)->handle($user, 'Acme');

        Livewire::actingAs($user)
            ->test('pages::accounts.create')
            ->set('type', 'asset')
            ->call('save')
            ->assertHasErrors(['name']);
    });

    it('fails validation when type is missing', function () {
        $user = User::factory()->create();
        app(CreateOrganization::class)->handle($user, 'Acme');

        Livewire::actingAs($user)
            ->test('pages::accounts.create')
            ->set('name', 'My Account')
            ->call('save')
            ->assertHasErrors(['type']);
    });

    it('fails validation when type is not a valid enum value', function () {
        $user = User::factory()->create();
        app(CreateOrganization::class)->handle($user, 'Acme');

        Livewire::actingAs($user)
            ->test('pages::accounts.create')
            ->set('name', 'My Account')
            ->set('type', 'invalid_type')
            ->call('save')
            ->assertHasErrors(['type']);
    });

    it('fails validation when currency is not exactly 3 characters', function () {
        $user = User::factory()->create();
        app(CreateOrganization::class)->handle($user, 'Acme');

        Livewire::actingAs($user)
            ->test('pages::accounts.create')
            ->set('name', 'My Account')
            ->set('type', 'asset')
            ->set('currency', 'US') // too short
            ->call('save')
            ->assertHasErrors(['currency']);
    });
});

describe('Accounts show page', function () {
    it('redirects guests to login', function () {
        $account = Account::factory()->create();
        $this->get(route('accounts.show', $account))->assertRedirect(route('login'));
    });

    it('returns 200 for an org member viewing their account', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');
        $acct = app(CreateAccount::class)->handle($org, 'Petty Cash', AccountType::Asset);

        $this->actingAs($user)->get(route('accounts.show', $acct))
            ->assertOk()
            ->assertSee('Petty Cash');
    });

    it('returns 403 for a non-member', function () {
        $outsider = User::factory()->create();
        app(CreateOrganization::class)->handle($outsider, 'My Org'); // gives outsider an org
        $account = Account::factory()->create(); // different org

        $this->actingAs($outsider)->get(route('accounts.show', $account))->assertForbidden();
    });

    it('shows the current balance on the account detail page', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');
        $acct = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

        $this->actingAs($user)->get(route('accounts.show', $acct))
            ->assertOk()
            ->assertSee('0.00'); // zero balance
    });
});
