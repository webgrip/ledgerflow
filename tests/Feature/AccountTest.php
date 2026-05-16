<?php

use App\Actions\CreateAccount;
use App\Actions\CreateOrganization;
use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// --- CreateAccount action ---

it('creates an account within an organization', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Acme');

    $account = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

    expect($account->organization_id)->toBe($org->id)
        ->and($account->name)->toBe('Checking')
        ->and($account->type)->toBe(AccountType::Asset)
        ->and($account->currency)->toBe('USD');
});

// --- AccountPolicy ---

it('allows an org member to view an account', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Acme');
    $account = app(CreateAccount::class)->handle($org, 'Savings', AccountType::Asset);

    expect($user->can('view', $account))->toBeTrue();
});

it('denies a non-member from viewing an account', function () {
    $outsider = User::factory()->create();
    $org = Organization::factory()->create();
    $account = Account::factory()->create(['organization_id' => $org->id]);

    expect($outsider->can('view', $account))->toBeFalse();
});

it('denies cross-org access to accounts', function () {
    $userA = User::factory()->create();
    $orgA = app(CreateOrganization::class)->handle($userA, 'Org A');
    $accountA = app(CreateAccount::class)->handle($orgA, 'Wallet', AccountType::Asset);

    $userB = User::factory()->create();
    app(CreateOrganization::class)->handle($userB, 'Org B');

    expect($userB->can('view', $accountA))->toBeFalse();
});

// --- Accounts index page ---

it('redirects guests from the accounts index', function () {
    $this->get(route('accounts.index'))->assertRedirect(route('login'));
});

it('shows accounts for the current organization', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'My Org');
    $account = app(CreateAccount::class)->handle($org, 'Main Checking', AccountType::Asset);

    $this->actingAs($user)->get(route('accounts.index'))
        ->assertOk()
        ->assertSee($account->name);
});

// --- Account create page ---

it('creates an account via the Livewire component', function () {
    $user = User::factory()->create();
    app(CreateOrganization::class)->handle($user, 'My Org');

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

it('validates required fields on account create', function () {
    $user = User::factory()->create();
    app(CreateOrganization::class)->handle($user, 'My Org');

    Livewire::actingAs($user)
        ->test('pages::accounts.create')
        ->call('save')
        ->assertHasErrors(['name', 'type']);
});

// --- Account show page ---

it('shows the account detail page', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'My Org');
    $account = app(CreateAccount::class)->handle($org, 'Petty Cash', AccountType::Asset);

    $this->actingAs($user)->get(route('accounts.show', $account))
        ->assertOk()
        ->assertSee('Petty Cash');
});

it('forbids non-members from viewing an account detail page', function () {
    $outsider = User::factory()->create();
    $org = Organization::factory()->create();
    $account = Account::factory()->create(['organization_id' => $org->id]);

    $this->actingAs($outsider)->get(route('accounts.show', $account))
        ->assertForbidden();
});
