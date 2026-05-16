<?php

use App\Actions\CreateAccount;
use App\Actions\CreateOrganization;
use App\Actions\RecordTransaction;
use App\Ai\Agents\TransactionExplainer;
use App\Enums\AccountType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Organization;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('provides an AI explanation for a transaction to an org member', function () {
    TransactionExplainer::fake(['This credit of $500.00 represents income to your account. Advisory: verify with your accountant.']);

    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Corp');
    $account = app(CreateAccount::class)->handle($org, 'Revenue', AccountType::Revenue);
    $tx = app(RecordTransaction::class)->handle($account, TransactionType::Credit, 50000, 'Client invoice');

    Livewire::actingAs($user)
        ->test('transactions.explain-button', ['transaction' => $tx])
        ->call('explain')
        ->assertHasNoErrors()
        ->assertSet('explanation', 'This credit of $500.00 represents income to your account. Advisory: verify with your accountant.')
        ->assertSet('error', null);

    TransactionExplainer::assertPrompted('Please explain this transaction.');
});

it('denies explanation to a user who cannot view the account', function () {
    TransactionExplainer::fake(['Some explanation.']);

    $outsider = User::factory()->create();
    app(CreateOrganization::class)->handle($outsider, 'Outsider Org');

    $org = Organization::factory()->create();
    $account = Account::factory()->create(['organization_id' => $org->id]);
    $tx = Transaction::factory()->create(['account_id' => $account->id]);

    Livewire::actingAs($outsider)
        ->test('transactions.explain-button', ['transaction' => $tx])
        ->call('explain')
        ->assertForbidden();

    TransactionExplainer::assertNeverPrompted();
});

it('shows an error state when the AI call fails', function () {
    TransactionExplainer::fake()->preventStrayPrompts();
    TransactionExplainer::fake([fn () => throw new RuntimeException('Provider unavailable')]);

    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Corp');
    $account = app(CreateAccount::class)->handle($org, 'Bank', AccountType::Asset);
    $tx = app(RecordTransaction::class)->handle($account, TransactionType::Debit, 1000, 'ATM');

    Livewire::actingAs($user)
        ->test('transactions.explain-button', ['transaction' => $tx])
        ->call('explain')
        ->assertSet('explanation', null)
        ->assertSet('error', 'AI explanation is temporarily unavailable. Please try again later.');
});
