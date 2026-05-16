<?php

declare(strict_types=1);

use App\Ai\Agents\AccountActivitySummarizer;
use App\Ai\Agents\ReconciliationAnalyst;
use App\Ai\Agents\TransactionCategorizer;
use App\Enums\OrganizationRole;
use App\Models\Account;
use App\Models\Organization;
use App\Models\ReconciliationIssue;
use App\Models\ReconciliationRun;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('TransactionCategorizer', function () {
    it('prompts the agent with transaction details', function () {
        TransactionCategorizer::fake([[
            'category' => 'revenue',
            'reason' => 'Incoming payment',
        ]]);

        $org = Organization::factory()->create();
        $account = Account::factory()->create(['organization_id' => $org->id]);
        $tx = Transaction::factory()->create(['account_id' => $account->id]);

        (new TransactionCategorizer($tx))->prompt('categorize');

        TransactionCategorizer::assertPrompted('categorize');
    });

    it('does not prompt for unrelated text when faked', function () {
        TransactionCategorizer::fake([[
            'category' => 'other',
            'reason' => 'Unknown',
        ]]);

        $tx = Transaction::factory()->create(['account_id' => Account::factory()->create(['organization_id' => Organization::factory()->create()->id])->id]);

        (new TransactionCategorizer($tx))->prompt('categorize');

        TransactionCategorizer::assertNotPrompted('this text was never sent');
    });
});

describe('AccountActivitySummarizer', function () {
    it('prompts the agent and returns a text summary', function () {
        AccountActivitySummarizer::fake(['This account has had moderate activity this month.']);

        $org = Organization::factory()->create();
        $account = Account::factory()->create(['organization_id' => $org->id]);

        $response = (new AccountActivitySummarizer($account))->prompt('summarize');

        expect($response->text)->toBe('This account has had moderate activity this month.');
        AccountActivitySummarizer::assertPrompted('summarize');
    });
});

describe('ReconciliationAnalyst', function () {
    it('prompts the agent with reconciliation issue context', function () {
        ReconciliationAnalyst::fake(['This issue is an unmatched event. Please review with your accountant.']);

        $org = Organization::factory()->create();
        $run = ReconciliationRun::factory()->create(['organization_id' => $org->id]);
        $issue = ReconciliationIssue::factory()->create([
            'reconciliation_run_id' => $run->id,
            'organization_id' => $org->id,
        ]);

        $response = (new ReconciliationAnalyst($issue))->prompt('explain');

        expect($response->text)->toContain('unmatched event');
        ReconciliationAnalyst::assertPrompted('explain');
    });
});

describe('AI explain buttons (Livewire)', function () {
    it('shows AI explanation on account activity summary component', function () {
        AccountActivitySummarizer::fake(['Account has been quiet this month.']);

        $owner = User::factory()->create();
        $org = Organization::factory()->create(['created_by' => $owner->id]);
        $org->memberships()->create(['user_id' => $owner->id, 'role' => OrganizationRole::Owner]);
        $owner->update(['current_organization_id' => $org->id]);

        $account = Account::factory()->create(['organization_id' => $org->id]);

        Livewire::actingAs($owner)
            ->test('accounts.activity-summary', ['account' => $account])
            ->call('summarize')
            ->assertSee('Account has been quiet this month.');
    });
});
