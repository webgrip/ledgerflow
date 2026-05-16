<?php

use App\Actions\CreateAccount;
use App\Actions\CreateOrganization;
use App\Actions\RecordTransaction;
use App\Enums\AccountType;
use App\Enums\TransactionType;
use App\Models\AuditEvent;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── AuditLogger service ────────────────────────────────────────────────────

it('records an audit event when an account is created', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Acme');
    $account = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset, 'USD', null, $user);

    $event = AuditEvent::where('event', 'account.created')->first();

    expect($event)->not->toBeNull()
        ->and($event->organization_id)->toBe($org->id)
        ->and($event->user_id)->toBe($user->id)
        ->and($event->subject_type)->toBe('App\\Models\\Account')
        ->and($event->subject_id)->toBe($account->id)
        ->and($event->metadata['name'])->toBe('Checking');
});

it('records an audit event when a transaction is recorded', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Acme');
    $account = app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset);
    $tx = app(RecordTransaction::class)->handle($account, TransactionType::Credit, 10000, 'Sale', null, $user);

    $event = AuditEvent::where('event', 'transaction.recorded')->first();

    expect($event)->not->toBeNull()
        ->and($event->subject_id)->toBe($tx->id)
        ->and($event->organization_id)->toBe($org->id)
        ->and($event->metadata['type'])->toBe('credit');
});

it('records an audit event when an organization is created', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Acme Corp');

    $event = AuditEvent::where('event', 'organization.created')->first();

    expect($event)->not->toBeNull()
        ->and($event->organization_id)->toBe($org->id)
        ->and($event->metadata['name'])->toBe('Acme Corp');
});

it('logs an event without a subject gracefully', function () {
    $event = AuditLogger::log(event: 'custom.event', metadata: ['note' => 'test']);

    expect($event->event)->toBe('custom.event')
        ->and($event->subject_type)->toBeNull()
        ->and($event->metadata['note'])->toBe('test');
});

it('relates audit events to their organization', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Acme');
    app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset, 'USD', null, $user);

    $event = AuditEvent::where('event', 'account.created')->first();

    expect($event->organization->name)->toBe('Acme');
});

it('relates audit events to their actor', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Acme');
    app(CreateAccount::class)->handle($org, 'Checking', AccountType::Asset, 'USD', null, $user);

    $event = AuditEvent::where('event', 'account.created')->first();

    expect($event->actor->id)->toBe($user->id);
});
