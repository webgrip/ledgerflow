<?php

use App\Actions\CreateAccount;
use App\Actions\CreateOrganization;
use App\Actions\RecordTransaction;
use App\Enums\AccountType;
use App\Enums\OrganizationRole;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── AccountPolicy — owner vs member ────────────────────────────────────────

it('allows an owner to create accounts', function () {
    $owner = User::factory()->create();
    app(CreateOrganization::class)->handle($owner, 'Acme');

    expect($owner->can('create', Account::class))->toBeTrue();
});

it('denies a member from creating accounts', function () {
    $owner = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($owner, 'Acme');

    $member = User::factory()->create(['current_organization_id' => $org->id]);
    $org->memberships()->create(['user_id' => $member->id, 'role' => OrganizationRole::Member]);

    expect($member->can('create', Account::class))->toBeFalse();
});

it('allows an owner to update accounts', function () {
    $owner = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($owner, 'Acme');
    $account = app(CreateAccount::class)->handle($org, 'Savings', AccountType::Asset);

    expect($owner->can('update', $account))->toBeTrue();
});

it('denies a member from updating accounts', function () {
    $owner = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($owner, 'Acme');
    $account = app(CreateAccount::class)->handle($org, 'Savings', AccountType::Asset);

    $member = User::factory()->create(['current_organization_id' => $org->id]);
    $org->memberships()->create(['user_id' => $member->id, 'role' => OrganizationRole::Member]);

    expect($member->can('update', $account))->toBeFalse();
});

it('allows an owner to delete accounts', function () {
    $owner = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($owner, 'Acme');
    $account = app(CreateAccount::class)->handle($org, 'Savings', AccountType::Asset);

    expect($owner->can('delete', $account))->toBeTrue();
});

it('denies a member from deleting accounts', function () {
    $owner = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($owner, 'Acme');
    $account = app(CreateAccount::class)->handle($org, 'Savings', AccountType::Asset);

    $member = User::factory()->create(['current_organization_id' => $org->id]);
    $org->memberships()->create(['user_id' => $member->id, 'role' => OrganizationRole::Member]);

    expect($member->can('delete', $account))->toBeFalse();
});

it('allows any org member to view accounts', function () {
    $owner = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($owner, 'Acme');
    $account = app(CreateAccount::class)->handle($org, 'Savings', AccountType::Asset);

    $member = User::factory()->create(['current_organization_id' => $org->id]);
    $org->memberships()->create(['user_id' => $member->id, 'role' => OrganizationRole::Member]);

    expect($member->can('view', $account))->toBeTrue();
});

// ── TransactionPolicy ─────────────────────────────────────────────────────

it('allows any org member to record transactions', function () {
    $owner = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($owner, 'Acme');

    $member = User::factory()->create(['current_organization_id' => $org->id]);
    $org->memberships()->create(['user_id' => $member->id, 'role' => OrganizationRole::Member]);

    expect($member->can('create', \App\Models\Transaction::class))->toBeTrue();
});

it('denies a member from deleting transactions', function () {
    $owner = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($owner, 'Acme');
    $account = app(CreateAccount::class)->handle($org, 'Savings', AccountType::Asset);
    $tx = app(RecordTransaction::class)->handle($account, TransactionType::Credit, 5000, 'Invoice');

    $member = User::factory()->create(['current_organization_id' => $org->id]);
    $org->memberships()->create(['user_id' => $member->id, 'role' => OrganizationRole::Member]);

    expect($member->can('delete', $tx))->toBeFalse();
});

it('allows an owner to delete transactions', function () {
    $owner = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($owner, 'Acme');
    $account = app(CreateAccount::class)->handle($org, 'Savings', AccountType::Asset);
    $tx = app(RecordTransaction::class)->handle($account, TransactionType::Credit, 5000, 'Invoice');

    expect($owner->can('delete', $tx))->toBeTrue();
});

// ── OrganizationMembership ────────────────────────────────────────────────

it('identifies the owner membership correctly', function () {
    $owner = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($owner, 'Acme');

    $membership = $org->memberships()->where('user_id', $owner->id)->first();

    expect($membership->isOwner())->toBeTrue();
});

it('identifies a member membership correctly', function () {
    $owner = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($owner, 'Acme');

    $member = User::factory()->create();
    $membership = $org->memberships()->create(['user_id' => $member->id, 'role' => OrganizationRole::Member]);

    expect($membership->isOwner())->toBeFalse();
});
