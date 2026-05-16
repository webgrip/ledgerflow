<?php

declare(strict_types=1);

use App\Actions\CreateAccount;
use App\Actions\CreateOrganization;
use App\Actions\RecordTransaction;
use App\Enums\AccountType;
use App\Enums\TransactionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows an org member to download a CSV export', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Acme');
    $account = app(CreateAccount::class)->handle($org, 'Revenue', AccountType::Revenue, 'EUR');

    app(RecordTransaction::class)->handle($account, TransactionType::Credit, 5000, 'Invoice #1', now(), $user);
    app(RecordTransaction::class)->handle($account, TransactionType::Credit, 3000, 'Invoice #2', now(), $user);

    $response = $this->actingAs($user)
        ->get(route('accounts.export', $account));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $csv = $response->streamedContent();

    expect($csv)
        ->toContain('Date,Description,Type,Amount,Currency')
        ->toContain('Invoice #1')
        ->toContain('Invoice #2')
        ->toContain('50.00')
        ->toContain('30.00');
});

it('returns 403 when a user from another org tries to export', function () {
    $owner = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($owner, 'Acme');
    $account = app(CreateAccount::class)->handle($org, 'Revenue', AccountType::Revenue);

    $other = User::factory()->create();
    app(CreateOrganization::class)->handle($other, 'Other Org');

    $this->actingAs($other)
        ->get(route('accounts.export', $account))
        ->assertForbidden();
});

it('exports an empty CSV with just the header when there are no transactions', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Acme');
    $account = app(CreateAccount::class)->handle($org, 'Empty', AccountType::Asset);

    $response = $this->actingAs($user)
        ->get(route('accounts.export', $account));

    $response->assertOk();

    $csv = $response->streamedContent();
    expect($csv)->toContain('Date,Description,Type,Amount,Currency');
    expect(substr_count($csv, "\n"))->toBe(1); // header row only
});
