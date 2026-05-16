<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CreateOrganization;
use App\Actions\RecordTransaction;
use App\Enums\AccountType;
use App\Enums\OrganizationRole;
use App\Enums\ReconciliationStatus;
use App\Enums\TransactionType;
use App\Enums\WebhookStatus;
use App\Models\Account;
use App\Models\ReconciliationIssue;
use App\Models\ReconciliationRun;
use App\Models\User;
use App\Models\WebhookEvent;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('e2e:seed {--fresh : Truncate all tables first}')]
#[Description('Seed deterministic E2E demo data (alice, bob, carol + orgs + accounts + transactions + webhooks + reconciliation).')]
class E2eSeedCommand extends Command
{
    public function handle(): int
    {
        if ($this->option('fresh')) {
            DB::statement('TRUNCATE TABLE reconciliation_issues, reconciliation_runs, provider_events, audit_events, transactions, accounts, organization_memberships, organizations, users RESTART IDENTITY CASCADE;');
            $this->line('🗑  Database truncated.');
        }

        DB::transaction(function (): void {
            // Users
            $alice = User::firstOrCreate(
                ['email' => 'alice@demo.test'],
                ['name' => 'Alice Founder', 'password' => bcrypt('password'), 'email_verified_at' => now()]
            );
            $bob = User::firstOrCreate(
                ['email' => 'bob@demo.test'],
                ['name' => 'Bob Accountant', 'password' => bcrypt('password'), 'email_verified_at' => now()]
            );
            $carol = User::firstOrCreate(
                ['email' => 'carol@demo.test'],
                ['name' => 'Carol CFO', 'password' => bcrypt('password'), 'email_verified_at' => now()]
            );

            foreach ([$alice, $bob, $carol] as $user) {
                if ($user->email_verified_at === null) {
                    $user->update(['email_verified_at' => now()]);
                }
            }

            // Orgs
            $org1 = app(CreateOrganization::class)->handle($alice, 'Acme Corp');
            $org1->memberships()->firstOrCreate(
                ['user_id' => $bob->id],
                ['role' => OrganizationRole::Member]
            );
            $alice->update(['current_organization_id' => $org1->id]);
            $bob->update(['current_organization_id' => $org1->id]);

            $org2 = app(CreateOrganization::class)->handle($carol, 'Globex LLC');
            $carol->update(['current_organization_id' => $org2->id]);

            // Accounts for Org 1
            $checking = Account::create(['organization_id' => $org1->id, 'name' => 'Main Checking', 'type' => AccountType::Asset, 'currency' => 'USD']);
            $payroll = Account::create(['organization_id' => $org1->id, 'name' => 'Payroll Expense', 'type' => AccountType::Expense, 'currency' => 'USD']);
            $revenue = Account::create(['organization_id' => $org1->id, 'name' => 'Sales Revenue', 'type' => AccountType::Revenue, 'currency' => 'USD']);

            // Accounts for Org 2
            $savings = Account::create(['organization_id' => $org2->id, 'name' => 'Reserve Fund', 'type' => AccountType::Asset, 'currency' => 'USD']);
            $opex = Account::create(['organization_id' => $org2->id, 'name' => 'Operating Expenses', 'type' => AccountType::Expense, 'currency' => 'USD']);

            // Transactions
            $recordTx = app(RecordTransaction::class);
            $txData = [
                [$checking, TransactionType::Credit, 500000, 'Initial deposit',       '-60 days'],
                [$revenue,  TransactionType::Credit, 120000, 'Q1 product sales',      '-55 days'],
                [$checking, TransactionType::Credit, 250000, 'Client invoice #1001',  '-45 days'],
                [$payroll,  TransactionType::Debit,  180000, 'Monthly payroll run',   '-30 days'],
                [$checking, TransactionType::Credit,  75000, 'Consulting retainer',   '-25 days'],
                [$payroll,  TransactionType::Debit,  180000, 'Monthly payroll run',    '0 days'],
                [$revenue,  TransactionType::Credit, 200000, 'Q2 product sales',      '-10 days'],
                [$savings,  TransactionType::Credit, 1000000, 'Seed round tranche',   '-50 days'],
                [$opex,     TransactionType::Debit,   30000, 'Cloud infrastructure',  '-20 days'],
                [$opex,     TransactionType::Debit,   15000, 'SaaS subscriptions',     '-5 days'],
                [$savings,  TransactionType::Credit, 300000, 'Investor top-up',        '-3 days'],
            ];

            foreach ($txData as [$account, $type, $amount, $desc, $daysAgo]) {
                $recordTx->handle(
                    account: $account,
                    type: $type,
                    amountMinorUnits: $amount,
                    description: $desc,
                    transactedAt: CarbonImmutable::now()->modify($daysAgo),
                );
            }

            // Webhook events for Org 1 (Stripe simulation)
            $webhookEvents = [
                ['stripe', 'stripe:evt_demo_001', 'payment_intent.succeeded', ['id' => 'evt_demo_001', 'type' => 'payment_intent.succeeded', 'amount' => 250000], WebhookStatus::Processed, '-45 days'],
                ['stripe', 'stripe:evt_demo_002', 'payment_intent.succeeded', ['id' => 'evt_demo_002', 'type' => 'payment_intent.succeeded', 'amount' => 75000], WebhookStatus::Processed, '-25 days'],
                ['stripe', 'stripe:evt_demo_003', 'payment_intent.succeeded', ['id' => 'evt_demo_003', 'type' => 'payment_intent.succeeded', 'amount' => 99900], WebhookStatus::Processed, '-7 days'],
                ['stripe', 'stripe:evt_demo_004', 'charge.refunded', ['id' => 'evt_demo_004', 'type' => 'charge.refunded', 'amount' => 15000], WebhookStatus::Failed, '-3 days'],
                ['stripe', 'stripe:evt_demo_005', 'payment_intent.succeeded', ['id' => 'evt_demo_005', 'type' => 'payment_intent.succeeded', 'amount' => 200000], WebhookStatus::Pending, '-1 days'],
            ];

            foreach ($webhookEvents as [$provider, $key, $eventType, $payload, $status, $daysAgo]) {
                WebhookEvent::create([
                    'organization_id' => $org1->id,
                    'provider' => $provider,
                    'idempotency_key' => $key,
                    'event_type' => $eventType,
                    'payload' => $payload,
                    'status' => $status,
                    'created_at' => CarbonImmutable::now()->modify($daysAgo),
                    'updated_at' => CarbonImmutable::now()->modify($daysAgo),
                ]);
            }

            // Reconciliation run for Org 1
            $run = ReconciliationRun::create([
                'organization_id' => $org1->id,
                'initiated_by' => $alice->id,
                'status' => ReconciliationStatus::Completed,
                'period_start' => CarbonImmutable::now()->subDays(60)->startOfMonth(),
                'period_end' => CarbonImmutable::now()->subDays(30)->endOfMonth(),
                'matched_count' => 2,
                'unmatched_count' => 2,
                'created_at' => CarbonImmutable::now()->subDays(28),
                'updated_at' => CarbonImmutable::now()->subDays(28),
            ]);

            ReconciliationIssue::insert([
                [
                    'reconciliation_run_id' => $run->id,
                    'organization_id' => $org1->id,
                    'issue_type' => 'unmatched_event',
                    'status' => 'open',
                    'details' => json_encode(['webhook_event_id' => 1, 'provider' => 'stripe', 'event_type' => 'payment_intent.succeeded', 'amount' => 99900]),
                    'created_at' => CarbonImmutable::now()->subDays(28),
                    'updated_at' => CarbonImmutable::now()->subDays(28),
                ],
                [
                    'reconciliation_run_id' => $run->id,
                    'organization_id' => $org1->id,
                    'issue_type' => 'unmatched_event',
                    'status' => 'resolved',
                    'details' => json_encode(['webhook_event_id' => 2, 'provider' => 'stripe', 'event_type' => 'charge.refunded', 'amount' => 15000]),
                    'created_at' => CarbonImmutable::now()->subDays(28),
                    'updated_at' => CarbonImmutable::now()->subDays(20),
                ],
            ]);
        });

        $this->info('✅  Demo data seeded:');
        $this->line('   • Users: alice@demo.test, bob@demo.test, carol@demo.test (password: password)');
        $this->line('   • Orgs: Acme Corp (alice + bob), Globex LLC (carol)');
        $this->line('   • 5 accounts + 11 transactions');
        $this->line('   • 5 Stripe webhook events (3 processed, 1 failed, 1 pending)');
        $this->line('   • 1 reconciliation run with 2 issues (1 open, 1 resolved)');

        return self::SUCCESS;
    }
}
