<?php

declare(strict_types=1);

use App\Actions\CreateAccount;
use App\Actions\CreateOrganization;
use App\Actions\RecordTransaction;
use App\Enums\AccountType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('Transaction create page', function () {
    it('redirects guests to login', function () {
        $account = Account::factory()->create();
        $this->get(route('transactions.create', $account))->assertRedirect(route('login'));
    });

    it('returns 200 for an org member', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');
        $acct = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

        $this->actingAs($user)->get(route('transactions.create', $acct))->assertOk();
    });

    it('returns 403 for a non-member accessing another org account', function () {
        $outsider = User::factory()->create();
        $account = Account::factory()->create();

        $this->actingAs($outsider)->get(route('transactions.create', $account))->assertForbidden();
    });

    it('records a credit transaction via the Livewire component', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');
        $acct = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

        Livewire::actingAs($user)
            ->test('pages::transactions.create', ['account' => $acct])
            ->set('type', 'credit')
            ->set('amount', '100.00')
            ->set('description', 'Test deposit')
            ->set('transactedAt', '2024-06-15')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirectContains('accounts');

        expect(Transaction::where('description', 'Test deposit')->exists())->toBeTrue();
    });

    it('records a debit transaction', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');
        $acct = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

        Livewire::actingAs($user)
            ->test('pages::transactions.create', ['account' => $acct])
            ->set('type', 'debit')
            ->set('amount', '50.00')
            ->set('description', 'Cash withdrawal')
            ->set('transactedAt', CarbonImmutable::now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transactions', [
            'account_id' => $acct->id,
            'type' => 'debit',
            'amount_minor_units' => 5000,
            'description' => 'Cash withdrawal',
        ]);
    });

    it('stores amounts as minor units (×100)', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');
        $acct = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

        Livewire::actingAs($user)
            ->test('pages::transactions.create', ['account' => $acct])
            ->set('type', 'credit')
            ->set('amount', '1234.56')
            ->set('description', 'Precise amount')
            ->set('transactedAt', CarbonImmutable::now()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transactions', [
            'amount_minor_units' => 123456,
        ]);
    });

    it('fails validation when amount is missing', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');
        $acct = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

        Livewire::actingAs($user)
            ->test('pages::transactions.create', ['account' => $acct])
            ->set('type', 'credit')
            ->set('description', 'No amount')
            ->set('transactedAt', CarbonImmutable::now()->toDateString())
            ->call('save')
            ->assertHasErrors(['amount']);
    });

    it('fails validation when description is missing', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');
        $acct = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

        Livewire::actingAs($user)
            ->test('pages::transactions.create', ['account' => $acct])
            ->set('type', 'credit')
            ->set('amount', '100.00')
            ->set('transactedAt', CarbonImmutable::now()->toDateString())
            ->call('save')
            ->assertHasErrors(['description']);
    });

    it('fails validation for a zero or negative amount', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');
        $acct = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

        Livewire::actingAs($user)
            ->test('pages::transactions.create', ['account' => $acct])
            ->set('type', 'credit')
            ->set('amount', '0')
            ->set('description', 'Zero amount')
            ->set('transactedAt', CarbonImmutable::now()->toDateString())
            ->call('save')
            ->assertHasErrors(['amount']);
    });
});

describe('Transaction show (account page with transactions)', function () {
    it('displays transactions on the account show page', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');
        $acct = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);

        app(RecordTransaction::class)->handle($acct, TransactionType::Credit, 5000, 'Visible Transaction');

        $this->actingAs($user)->get(route('accounts.show', $acct))
            ->assertOk()
            ->assertSee('Visible Transaction');
    });
});
