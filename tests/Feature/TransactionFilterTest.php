<?php

use App\Actions\CreateAccount;
use App\Actions\CreateOrganization;
use App\Actions\RecordTransaction;
use App\Enums\AccountType;
use App\Enums\TransactionType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeAccountWithTransactions(): array
{
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Acme');
    $account = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

    $t1 = app(RecordTransaction::class)->handle($account, TransactionType::Credit, 10000, 'Invoice paid', CarbonImmutable::parse('2024-01-15'));
    $t2 = app(RecordTransaction::class)->handle($account, TransactionType::Debit, 5000, 'Office rent', CarbonImmutable::parse('2024-02-10'));
    $t3 = app(RecordTransaction::class)->handle($account, TransactionType::Credit, 2500, 'Refund received', CarbonImmutable::parse('2024-03-01'));

    return [$user, $account, $t1, $t2, $t3];
}

it('shows all transactions when no filters are applied', function () {
    [$user, $account, $t1, $t2, $t3] = makeAccountWithTransactions();

    Livewire::actingAs($user)
        ->test('pages::accounts.show', ['account' => $account])
        ->assertSee('Invoice paid')
        ->assertSee('Office rent')
        ->assertSee('Refund received');
});

it('filters transactions by description search', function () {
    [$user, $account] = makeAccountWithTransactions();

    Livewire::actingAs($user)
        ->test('pages::accounts.show', ['account' => $account])
        ->set('search', 'Invoice')
        ->assertSee('Invoice paid')
        ->assertDontSee('Office rent')
        ->assertDontSee('Refund received');
});

it('filters transactions by type', function () {
    [$user, $account] = makeAccountWithTransactions();

    Livewire::actingAs($user)
        ->test('pages::accounts.show', ['account' => $account])
        ->set('type', 'debit')
        ->assertDontSee('Invoice paid')
        ->assertSee('Office rent')
        ->assertDontSee('Refund received');
});

it('filters transactions by date range', function () {
    [$user, $account] = makeAccountWithTransactions();

    Livewire::actingAs($user)
        ->test('pages::accounts.show', ['account' => $account])
        ->set('dateFrom', '2024-01-01')
        ->set('dateTo', '2024-01-31')
        ->assertSee('Invoice paid')
        ->assertDontSee('Office rent')
        ->assertDontSee('Refund received');
});

it('shows no-results message when filters match nothing', function () {
    [$user, $account] = makeAccountWithTransactions();

    Livewire::actingAs($user)
        ->test('pages::accounts.show', ['account' => $account])
        ->set('search', 'nonexistent xyz')
        ->assertSee('No matching transactions');
});

it('clears all filters on clearFilters call', function () {
    [$user, $account] = makeAccountWithTransactions();

    Livewire::actingAs($user)
        ->test('pages::accounts.show', ['account' => $account])
        ->set('search', 'Invoice')
        ->set('type', 'credit')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('type', '')
        ->assertSee('Invoice paid')
        ->assertSee('Office rent');
});
