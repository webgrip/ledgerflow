<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Enums\TransactionType;
use App\Mcp\Tools\GetAccountSummary;
use App\Mcp\Tools\ListAuditEvents;
use App\Mcp\Tools\ListReconciliationIssues;
use App\Mcp\Tools\SearchTransactions;
use App\Models\Account;
use App\Models\Organization;
use App\Models\ReconciliationIssue;
use App\Models\ReconciliationRun;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Request;

uses(RefreshDatabase::class);

function makeMcpUser(): array
{
    $owner = User::factory()->create();
    $org = Organization::factory()->create(['created_by' => $owner->id]);
    $org->memberships()->create(['user_id' => $owner->id, 'role' => OrganizationRole::Owner]);
    $owner->update(['current_organization_id' => $org->id]);

    return [$owner, $org];
}

describe('GetAccountSummary tool', function () {
    it('returns account summaries for the current org', function () {
        [$user, $org] = makeMcpUser();
        Account::factory()->count(2)->create(['organization_id' => $org->id]);

        Auth::login($user);

        $response = (new GetAccountSummary)->handle(new Request);

        expect(((string) $response->content()))->toContain("Accounts for organization #{$org->id}");
    });

    it('returns a friendly message when org has no accounts', function () {
        [$user] = makeMcpUser();
        Auth::login($user);

        $response = (new GetAccountSummary)->handle(new Request);

        expect(((string) $response->content()))->toContain('No accounts found');
    });
});

describe('SearchTransactions tool', function () {
    it('returns transactions for the current org', function () {
        [$user, $org] = makeMcpUser();
        $account = Account::factory()->create(['organization_id' => $org->id]);
        Transaction::factory()->count(3)->create(['account_id' => $account->id]);

        Auth::login($user);

        $response = (new SearchTransactions)->handle(new Request);

        expect(((string) $response->content()))->toContain('Found 3 transaction(s)');
    });

    it('filters transactions by type', function () {
        [$user, $org] = makeMcpUser();
        $account = Account::factory()->create(['organization_id' => $org->id]);
        Transaction::factory()->create(['account_id' => $account->id, 'type' => TransactionType::Credit]);
        Transaction::factory()->create(['account_id' => $account->id, 'type' => TransactionType::Debit]);

        Auth::login($user);

        $request = new Request(['type' => 'credit']);
        $response = (new SearchTransactions)->handle($request);

        expect(((string) $response->content()))->toContain('Found 1 transaction(s)');
    });

    it('filters transactions by description search', function () {
        [$user, $org] = makeMcpUser();
        $account = Account::factory()->create(['organization_id' => $org->id]);
        Transaction::factory()->create(['account_id' => $account->id, 'description' => 'Stripe payment ABC']);
        Transaction::factory()->create(['account_id' => $account->id, 'description' => 'Manual entry']);

        Auth::login($user);

        $request = new Request(['search' => 'Stripe']);
        $response = (new SearchTransactions)->handle($request);

        expect(((string) $response->content()))->toContain('Found 1 transaction(s)');
    });
});

describe('ListReconciliationIssues tool', function () {
    it('returns open issues for the current org', function () {
        [$user, $org] = makeMcpUser();
        $run = ReconciliationRun::factory()->create(['organization_id' => $org->id]);
        ReconciliationIssue::factory()->count(2)->create([
            'reconciliation_run_id' => $run->id,
            'organization_id' => $org->id,
        ]);

        Auth::login($user);

        $response = (new ListReconciliationIssues)->handle(new Request);

        expect(((string) $response->content()))->toContain('Found 2 issue(s)');
    });

    it('returns a friendly message when there are no issues', function () {
        [$user] = makeMcpUser();
        Auth::login($user);

        $response = (new ListReconciliationIssues)->handle(new Request);

        expect(((string) $response->content()))->toContain('No reconciliation issues found');
    });
});

describe('ListAuditEvents tool', function () {
    it('returns audit events for the current org', function () {
        [$user, $org] = makeMcpUser();

        AuditLogger::log('test.event', organizationId: $org->id, userId: $user->id);
        AuditLogger::log('another.event', organizationId: $org->id, userId: $user->id);

        Auth::login($user);

        $response = (new ListAuditEvents)->handle(new Request);

        expect(((string) $response->content()))->toContain('Found 2 audit event(s)');
    });

    it('filters by event name', function () {
        [$user, $org] = makeMcpUser();

        AuditLogger::log('transaction.created', organizationId: $org->id, userId: $user->id);
        AuditLogger::log('account.created', organizationId: $org->id, userId: $user->id);

        Auth::login($user);

        $request = new Request(['event' => 'transaction']);
        $response = (new ListAuditEvents)->handle($request);

        expect(((string) $response->content()))->toContain('Found 1 audit event(s)');
    });
});

describe('MCP endpoint authorization', function () {
    it('requires authentication to access the MCP endpoint', function () {
        $this->postJson('/mcp/ledgerflow', [])->assertUnauthorized();
    });
});
