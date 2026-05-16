<?php

declare(strict_types=1);

use App\Actions\RunReconciliation;
use App\Enums\OrganizationRole;
use App\Enums\ReconciliationIssueType;
use App\Enums\ReconciliationStatus;
use App\Enums\TransactionType;
use App\Enums\WebhookStatus;
use App\Models\Account;
use App\Models\Organization;
use App\Models\ReconciliationIssue;
use App\Models\ReconciliationRun;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WebhookEvent;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeOrgWithOwner(): array
{
    $owner = User::factory()->create();
    $org = Organization::factory()->create(['created_by' => $owner->id]);
    $org->memberships()->create(['user_id' => $owner->id, 'role' => OrganizationRole::Owner]);
    $owner->update(['current_organization_id' => $org->id]);

    return [$org, $owner];
}

describe('RunReconciliation action', function () {
    it('creates a completed run with no issues when there are no webhook events', function () {
        [$org, $owner] = makeOrgWithOwner();
        $period = CarbonImmutable::now();

        $run = app(RunReconciliation::class)->handle(
            organization: $org,
            periodStart: $period->startOfMonth(),
            periodEnd: $period->endOfMonth(),
            initiator: $owner,
        );

        expect($run->status)->toBe(ReconciliationStatus::Completed)
            ->and($run->matched_count)->toBe(0)
            ->and($run->unmatched_count)->toBe(0)
            ->and($run->issues()->count())->toBe(0);
    });

    it('flags an unmatched webhook event as an issue', function () {
        [$org, $owner] = makeOrgWithOwner();

        WebhookEvent::factory()->create([
            'organization_id' => $org->id,
            'status' => WebhookStatus::Processed,
            'payload' => ['amount' => 5000],
            'created_at' => now(),
        ]);

        $run = app(RunReconciliation::class)->handle(
            organization: $org,
            periodStart: CarbonImmutable::now()->startOfMonth(),
            periodEnd: CarbonImmutable::now()->endOfMonth(),
            initiator: $owner,
        );

        expect($run->unmatched_count)->toBe(1)
            ->and($run->issues()->where('issue_type', ReconciliationIssueType::UnmatchedEvent)->count())->toBe(1);
    });

    it('does not flag a webhook event that has a matching transaction', function () {
        [$org, $owner] = makeOrgWithOwner();
        $account = Account::factory()->create(['organization_id' => $org->id]);

        WebhookEvent::factory()->create([
            'organization_id' => $org->id,
            'status' => WebhookStatus::Processed,
            'payload' => ['amount' => 2500],
            'created_at' => now(),
        ]);

        Transaction::factory()->create([
            'account_id' => $account->id,
            'amount_minor_units' => 2500,
            'type' => TransactionType::Credit,
            'transacted_at' => now(),
        ]);

        $run = app(RunReconciliation::class)->handle(
            organization: $org,
            periodStart: CarbonImmutable::now()->startOfMonth(),
            periodEnd: CarbonImmutable::now()->endOfMonth(),
            initiator: $owner,
        );

        expect($run->matched_count)->toBe(1)
            ->and($run->unmatched_count)->toBe(0);
    });

    it('persists run with correct organization and period', function () {
        [$org, $owner] = makeOrgWithOwner();
        $start = CarbonImmutable::parse('2024-01-01');
        $end = CarbonImmutable::parse('2024-01-31');

        $run = app(RunReconciliation::class)->handle($org, $start, $end, $owner);

        expect($run->organization_id)->toBe($org->id)
            ->and($run->initiated_by)->toBe($owner->id)
            ->and($run->period_start->toDateString())->toBe('2024-01-01')
            ->and($run->period_end->toDateString())->toBe('2024-01-31');
    });
});

describe('ReconciliationRun model', function () {
    it('counts only open issues', function () {
        $run = ReconciliationRun::factory()->create();
        ReconciliationIssue::factory()->count(2)->create(['reconciliation_run_id' => $run->id, 'organization_id' => $run->organization_id]);
        ReconciliationIssue::factory()->resolved()->create(['reconciliation_run_id' => $run->id, 'organization_id' => $run->organization_id]);

        expect($run->openIssuesCount())->toBe(2);
    });
});

describe('Reconciliation pages', function () {
    it('shows reconciliation index for an authenticated user', function () {
        [$org, $owner] = makeOrgWithOwner();

        $this->actingAs($owner)->get('/reconciliation')
            ->assertOk()
            ->assertSee('Reconciliation');
    });

    it('redirects unauthenticated users away from reconciliation index', function () {
        $this->get('/reconciliation')->assertRedirect('/login');
    });

    it('shows a specific reconciliation run', function () {
        [$org, $owner] = makeOrgWithOwner();
        $run = ReconciliationRun::factory()->create(['organization_id' => $org->id, 'initiated_by' => $owner->id]);

        $this->actingAs($owner)->get("/reconciliation/{$run->id}")
            ->assertOk();
    });

    it('returns 403 for a run belonging to a different organization', function () {
        [$org, $owner] = makeOrgWithOwner();
        [$otherOrg] = makeOrgWithOwner();

        $run = ReconciliationRun::factory()->create(['organization_id' => $otherOrg->id]);

        $this->actingAs($owner)->get("/reconciliation/{$run->id}")
            ->assertForbidden();
    });
});
