<?php

use App\Actions\CreateAccount;
use App\Actions\CreateOrganization;
use App\Actions\RecordTransaction;
use App\Enums\AccountType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Organization;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// --- RecordTransaction action ---

it('records a credit transaction', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Corp');
    $account = app(CreateAccount::class)->handle($org, 'Revenue', AccountType::Revenue);

    $tx = app(RecordTransaction::class)->handle(
        account: $account,
        type: TransactionType::Credit,
        amountMinorUnits: 50000,
        description: 'Invoice payment',
        transactedAt: CarbonImmutable::parse('2024-01-15'),
    );

    expect($tx)->toBeInstanceOf(Transaction::class)
        ->and($tx->type)->toBe(TransactionType::Credit)
        ->and($tx->amount_minor_units)->toBe(50000)
        ->and($tx->description)->toBe('Invoice payment');
});

it('records a debit transaction', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Corp');
    $account = app(CreateAccount::class)->handle($org, 'Expenses', AccountType::Expense);

    $tx = app(RecordTransaction::class)->handle(
        account: $account,
        type: TransactionType::Debit,
        amountMinorUnits: 12099,
        description: 'Office supplies',
    );

    expect($tx->type)->toBe(TransactionType::Debit)
        ->and($tx->amount_minor_units)->toBe(12099);
});

// --- Balance calculation ---

it('calculates balance as credits minus debits', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Corp');
    $account = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);
    $record = app(RecordTransaction::class);

    $record->handle($account, TransactionType::Credit, 100000, 'Deposit');
    $record->handle($account, TransactionType::Credit, 50000, 'Transfer in');
    $record->handle($account, TransactionType::Debit, 30000, 'Withdrawal');

    expect($account->balance())->toBe(120000); // 150000 - 30000
});

it('returns zero balance for account with no transactions', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Corp');
    $account = app(CreateAccount::class)->handle($org, 'Empty', AccountType::Asset);

    expect($account->balance())->toBe(0);
});

// --- Transaction create page ---

it('redirects guests from the transaction create page', function () {
    $account = Account::factory()->create();

    $this->get(route('transactions.create', $account))->assertRedirect(route('login'));
});

it('denies non-members from recording a transaction', function () {
    $outsider = User::factory()->create();
    app(CreateOrganization::class)->handle($outsider, 'Outsider Org');
    $org = Organization::factory()->create();
    $account = Account::factory()->create(['organization_id' => $org->id]);

    Livewire::actingAs($outsider)
        ->test('pages::transactions.create', ['account' => $account])
        ->assertForbidden();
});

it('records a transaction via the Livewire component', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Corp');
    $account = app(CreateAccount::class)->handle($org, 'Bank', AccountType::Asset);

    Livewire::actingAs($user)
        ->test('pages::transactions.create', ['account' => $account])
        ->set('type', 'credit')
        ->set('amount', '100.50')
        ->set('description', 'Client payment')
        ->set('transactedAt', '2024-06-01')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('accounts.show', $account));

    expect(Transaction::where('description', 'Client payment')->exists())->toBeTrue();
    expect(Transaction::where('description', 'Client payment')->first()->amount_minor_units)->toBe(10050);
});

it('validates required fields on transaction create', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Corp');
    $account = app(CreateAccount::class)->handle($org, 'Bank', AccountType::Asset);

    Livewire::actingAs($user)
        ->test('pages::transactions.create', ['account' => $account])
        ->set('amount', '')
        ->set('description', '')
        ->call('save')
        ->assertHasErrors(['amount', 'description']);
});
