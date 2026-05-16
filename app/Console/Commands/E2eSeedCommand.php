<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CreateOrganization;
use App\Actions\RecordTransaction;
use App\Enums\AccountType;
use App\Enums\OrganizationRole;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('e2e:seed {--fresh : Truncate all tables first}')]
#[Description('Seed deterministic E2E demo data (alice, bob, carol + orgs + accounts + transactions).')]
class E2eSeedCommand extends Command
{
    public function handle(): int
    {
        if ($this->option('fresh')) {
            DB::statement('TRUNCATE TABLE transactions, accounts, organization_memberships, organizations, users RESTART IDENTITY CASCADE;');
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

            // Ensure email_verified_at is set (in case they already existed)
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
            $bob->update(['current_organization_id' => $org1->id]);

            $org2 = app(CreateOrganization::class)->handle($carol, 'Globex LLC');

            // Accounts for Org 1
            $checking = Account::create(['organization_id' => $org1->id, 'name' => 'Main Checking', 'type' => AccountType::Asset, 'currency' => 'USD']);
            $payroll = Account::create(['organization_id' => $org1->id, 'name' => 'Payroll Expense', 'type' => AccountType::Expense, 'currency' => 'USD']);
            $revenue = Account::create(['organization_id' => $org1->id, 'name' => 'Sales Revenue', 'type' => AccountType::Revenue, 'currency' => 'USD']);

            // Accounts for Org 2
            $savings = Account::create(['organization_id' => $org2->id, 'name' => 'Reserve Fund', 'type' => AccountType::Asset, 'currency' => 'USD']);
            $opex = Account::create(['organization_id' => $org2->id, 'name' => 'Operating Expenses', 'type' => AccountType::Expense, 'currency' => 'USD']);

            // Transactions
            $recordTx = app(RecordTransaction::class);
            foreach ([
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
            ] as [$account, $type, $amount, $desc, $daysAgo]) {
                $recordTx->handle(
                    account: $account,
                    type: $type,
                    amountMinorUnits: $amount,
                    description: $desc,
                    transactedAt: CarbonImmutable::now()->modify($daysAgo),
                );
            }
        });

        $this->info('✅  Demo data seeded: alice, bob, carol + 2 orgs + 5 accounts + 11 transactions.');

        return self::SUCCESS;
    }
}
