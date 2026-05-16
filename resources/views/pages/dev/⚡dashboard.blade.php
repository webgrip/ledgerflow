<?php

declare(strict_types=1);

use App\Actions\CreateOrganization;
use App\Actions\RecordTransaction;
use App\Enums\AccountType;
use App\Enums\OrganizationRole;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Organization;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dev Dashboard')] #[Layout('layouts.dev')] class extends Component {
    public string $activeTab = 'overview';
    public string $txSearch = '';
    public bool $autoRefresh = false;
    public ?string $flashMessage = null;
    public string $flashType = 'success';

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ── Computed Stats ──────────────────────────────────────────────────────

    #[Computed]
    public function stats(): array
    {
        $totalCredits = Transaction::where('type', TransactionType::Credit)->sum('amount_minor_units');
        $totalDebits = Transaction::where('type', TransactionType::Debit)->sum('amount_minor_units');

        return [
            'users' => User::count(),
            'organizations' => Organization::count(),
            'accounts' => Account::count(),
            'transactions' => Transaction::count(),
            'total_credits' => (int) $totalCredits,
            'total_debits' => (int) $totalDebits,
            'net_flow' => (int) ($totalCredits - $totalDebits),
        ];
    }

    #[Computed]
    public function users(): \Illuminate\Database\Eloquent\Collection
    {
        return User::with(['currentOrganization', 'organizations'])->latest()->get();
    }

    #[Computed]
    public function organizations(): \Illuminate\Database\Eloquent\Collection
    {
        return Organization::with(['creator', 'memberships.user', 'memberships' => function ($q) {
            $q->with('user');
        }])
            ->withCount(['memberships', 'memberships as accounts_count'])
            ->latest()
            ->get()
            ->map(function (Organization $org) {
                $org->accounts_list = Account::where('organization_id', $org->id)
                    ->withCount('transactions')
                    ->get();
                return $org;
            });
    }

    #[Computed]
    public function accounts(): \Illuminate\Database\Eloquent\Collection
    {
        return Account::with(['organization', 'transactions'])
            ->withCount('transactions')
            ->orderBy('organization_id')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function transactions(): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::with(['account.organization'])
            ->when($this->txSearch, function ($q) {
                $q->where('description', 'like', '%' . $this->txSearch . '%');
            })
            ->orderByDesc('transacted_at')
            ->limit(100)
            ->get();
    }

    #[Computed]
    public function accountTypeBreakdown(): array
    {
        return Account::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->type->value => $row->count])
            ->all();
    }

    #[Computed]
    public function txVolumeByDay(): array
    {
        return Transaction::select(
            DB::raw('DATE(transacted_at) as day'),
            DB::raw("SUM(CASE WHEN type = 'credit' THEN amount_minor_units ELSE 0 END) as credits"),
            DB::raw("SUM(CASE WHEN type = 'debit' THEN amount_minor_units ELSE 0 END) as debits")
        )
            ->where('transacted_at', '>=', now()->subDays(30))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day')
            ->all();
    }

    #[Computed]
    public function systemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => config('app.env'),
            'debug' => config('app.debug') ? 'enabled' : 'disabled',
            'db_driver' => config('database.default'),
            'db_name' => config('database.connections.' . config('database.default') . '.database'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'timezone' => config('app.timezone'),
        ];
    }

    // ── Actions ─────────────────────────────────────────────────────────────

    public function seedDemoData(): void
    {
        DB::transaction(function () {
            // Create 3 users
            $users = collect();
            foreach ([
                ['Alice Founder', 'alice@demo.test'],
                ['Bob Accountant', 'bob@demo.test'],
                ['Carol CFO', 'carol@demo.test'],
            ] as [$name, $email]) {
                $users->push(
                    User::firstOrCreate(['email' => $email], [
                        'name' => $name,
                        'password' => bcrypt('password'),
                        'email_verified_at' => now(),
                    ])
                );
            }

            // Org 1 — Alice owns it, Bob is a member
            $org1 = app(CreateOrganization::class)->handle($users[0], 'Acme Corp');
            $org1->memberships()->firstOrCreate(
                ['user_id' => $users[1]->id],
                ['role' => OrganizationRole::Member]
            );
            $users[1]->update(['current_organization_id' => $org1->id]);

            // Org 2 — Carol owns it
            $org2 = app(CreateOrganization::class)->handle($users[2], 'Globex LLC');

            // Accounts for Org 1
            $checking = Account::firstOrCreate(
                ['organization_id' => $org1->id, 'name' => 'Main Checking'],
                ['type' => AccountType::Asset, 'currency' => 'USD']
            );
            $payroll = Account::firstOrCreate(
                ['organization_id' => $org1->id, 'name' => 'Payroll Expense'],
                ['type' => AccountType::Expense, 'currency' => 'USD']
            );
            $revenue = Account::firstOrCreate(
                ['organization_id' => $org1->id, 'name' => 'Sales Revenue'],
                ['type' => AccountType::Revenue, 'currency' => 'USD']
            );

            // Accounts for Org 2
            $savings = Account::firstOrCreate(
                ['organization_id' => $org2->id, 'name' => 'Reserve Fund'],
                ['type' => AccountType::Asset, 'currency' => 'USD']
            );
            $opex = Account::firstOrCreate(
                ['organization_id' => $org2->id, 'name' => 'Operating Expenses'],
                ['type' => AccountType::Expense, 'currency' => 'USD']
            );

            // Seed transactions
            $txData = [
                [$checking, TransactionType::Credit, 500000, 'Initial deposit', '-60 days'],
                [$revenue, TransactionType::Credit, 120000, 'Q1 product sales', '-55 days'],
                [$checking, TransactionType::Credit, 250000, 'Client invoice #1001', '-45 days'],
                [$payroll, TransactionType::Debit, 180000, 'Monthly payroll run', '-30 days'],
                [$checking, TransactionType::Credit, 75000, 'Consulting retainer', '-25 days'],
                [$payroll, TransactionType::Debit, 180000, 'Monthly payroll run', '-0 days'],
                [$revenue, TransactionType::Credit, 200000, 'Q2 product sales', '-10 days'],
                [$savings, TransactionType::Credit, 1000000, 'Seed round tranche', '-50 days'],
                [$opex, TransactionType::Debit, 30000, 'Cloud infrastructure', '-20 days'],
                [$opex, TransactionType::Debit, 15000, 'SaaS subscriptions', '-5 days'],
                [$savings, TransactionType::Credit, 300000, 'Investor top-up', '-3 days'],
            ];

            $recordTx = app(RecordTransaction::class);
            foreach ($txData as [$account, $type, $amount, $desc, $daysAgo]) {
                $recordTx->handle(
                    account: $account,
                    type: $type,
                    amountMinorUnits: $amount,
                    description: $desc,
                    transactedAt: CarbonImmutable::now()->modify($daysAgo . ' days')
                );
            }
        });

        unset($this->stats, $this->users, $this->organizations, $this->accounts, $this->transactions, $this->accountTypeBreakdown, $this->txVolumeByDay);
        $this->flash('Demo data seeded successfully!', 'success');
    }

    public function nukeDatabase(): void
    {
        DB::statement('TRUNCATE TABLE transactions, accounts, organization_memberships, organizations, users RESTART IDENTITY CASCADE;');

        unset($this->stats, $this->users, $this->organizations, $this->accounts, $this->transactions, $this->accountTypeBreakdown, $this->txVolumeByDay);
        $this->flash('Database cleared.', 'warning');
    }

    public function refreshAll(): void
    {
        unset($this->stats, $this->users, $this->organizations, $this->accounts, $this->transactions, $this->accountTypeBreakdown, $this->txVolumeByDay);
        $this->flash('Data refreshed.', 'info');
    }

    private function flash(string $message, string $type = 'success'): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
    }

    public function dismissFlash(): void
    {
        $this->flashMessage = null;
    }
}; ?>

<div class="min-h-screen">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <header class="border-b border-zinc-800 bg-zinc-900/80 backdrop-blur sticky top-8 z-40">
        <div class="max-w-screen-2xl mx-auto px-6 py-4 flex items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="size-8 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold">LF</div>
                <div>
                    <div class="text-sm font-bold text-zinc-100">LedgerFlow</div>
                    <div class="text-xs text-zinc-500 font-mono">dev dashboard</div>
                </div>
            </div>

            {{-- Tabs --}}
            <nav class="flex gap-1 ml-8">
                @foreach ([
                    ['overview',      'Overview',      '◈'],
                    ['organizations', 'Orgs',          '🏢'],
                    ['accounts',      'Accounts',      '💳'],
                    ['transactions',  'Transactions',  '↕'],
                    ['users',         'Users',         '👤'],
                    ['system',        'System',        '⚙'],
                ] as [$key, $label, $icon])
                    <button
                        wire:click="setTab('{{ $key }}')"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                            {{ $activeTab === $key
                                ? 'bg-violet-500/20 text-violet-300 border border-violet-500/30'
                                : 'text-zinc-500 hover:text-zinc-300 hover:bg-zinc-800' }}"
                    >
                        {{ $icon }} {{ $label }}
                    </button>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-2">
                {{-- Auto-refresh toggle --}}
                <label class="flex items-center gap-2 text-xs text-zinc-500 cursor-pointer select-none" wire:poll.keep-alive="{{ $autoRefresh ? '5000ms' : '99999s' }}.refreshAll">
                    <div
                        class="relative inline-flex h-4 w-7 cursor-pointer rounded-full transition-colors {{ $autoRefresh ? 'bg-violet-500' : 'bg-zinc-700' }}"
                        wire:click="$toggle('autoRefresh')"
                    >
                        <span class="inline-block h-3 w-3 rounded-full bg-white shadow transition-transform mt-0.5 {{ $autoRefresh ? 'translate-x-3.5' : 'translate-x-0.5' }}"></span>
                    </div>
                    Auto-refresh
                </label>

                <button
                    wire:click="refreshAll"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-zinc-800 text-zinc-300 hover:bg-zinc-700 transition-colors border border-zinc-700"
                >
                    <span wire:loading.remove wire:target="refreshAll">↻ Refresh</span>
                    <span wire:loading wire:target="refreshAll">…</span>
                </button>
            </div>
        </div>
    </header>

    {{-- Flash message --}}
    @if ($flashMessage)
        <div class="max-w-screen-2xl mx-auto px-6 pt-4">
            <div class="flex items-center justify-between rounded-lg px-4 py-3 text-sm font-medium border
                {{ $flashType === 'success' ? 'bg-green-500/10 border-green-500/30 text-green-300' : '' }}
                {{ $flashType === 'warning' ? 'bg-amber-500/10 border-amber-500/30 text-amber-300' : '' }}
                {{ $flashType === 'info' ? 'bg-blue-500/10 border-blue-500/30 text-blue-300' : '' }}"
            >
                {{ $flashMessage }}
                <button wire:click="dismissFlash" class="text-inherit opacity-60 hover:opacity-100 ml-4">✕</button>
            </div>
        </div>
    @endif

    <main class="max-w-screen-2xl mx-auto px-6 py-6 space-y-6">

        {{-- ── KPI Cards (always visible) ──────────────────────────────────── --}}
        <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            @foreach ([
                ['Users',         $this->stats['users'],                        'bg-violet-500/10 border-violet-500/20 text-violet-300', '👤'],
                ['Orgs',          $this->stats['organizations'],                 'bg-indigo-500/10 border-indigo-500/20 text-indigo-300',  '🏢'],
                ['Accounts',      $this->stats['accounts'],                      'bg-blue-500/10 border-blue-500/20 text-blue-300',        '💳'],
                ['Transactions',  $this->stats['transactions'],                  'bg-cyan-500/10 border-cyan-500/20 text-cyan-300',        '↕'],
                ['Total Credits', '$'.number_format($this->stats['total_credits']/100, 2), 'bg-green-500/10 border-green-500/20 text-green-300',  '↑'],
                ['Total Debits',  '$'.number_format($this->stats['total_debits']/100, 2),  'bg-red-500/10 border-red-500/20 text-red-300',       '↓'],
                ['Net Flow',      '$'.number_format($this->stats['net_flow']/100, 2),      $this->stats['net_flow'] >= 0 ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-300' : 'bg-rose-500/10 border-rose-500/20 text-rose-300', '≈'],
            ] as [$label, $value, $classes, $icon])
                <div class="rounded-xl border p-3 {{ $classes }}">
                    <div class="text-xs opacity-60 mb-1">{{ $icon }} {{ $label }}</div>
                    <div class="text-lg font-bold font-mono tracking-tight">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        {{-- ── Quick Actions ────────────────────────────────────────────────── --}}
        <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-4">
            <div class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-3">⚡ Quick Actions</div>
            <div class="flex flex-wrap gap-2">
                <button
                    wire:click="seedDemoData"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 rounded-lg text-sm font-medium bg-violet-500/20 hover:bg-violet-500/30 text-violet-300 border border-violet-500/30 transition-colors disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="seedDemoData">🌱 Seed Demo Data</span>
                    <span wire:loading wire:target="seedDemoData">Seeding…</span>
                </button>

                <a href="{{ route('accounts.index') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium bg-zinc-800 hover:bg-zinc-700 text-zinc-300 border border-zinc-700 transition-colors">
                    💳 Accounts UI
                </a>

                <a href="{{ route('dashboard') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium bg-zinc-800 hover:bg-zinc-700 text-zinc-300 border border-zinc-700 transition-colors">
                    🏠 App Dashboard
                </a>

                <a href="{{ route('login') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium bg-zinc-800 hover:bg-zinc-700 text-zinc-300 border border-zinc-700 transition-colors">
                    🔐 Login Page
                </a>

                <a href="{{ route('register') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium bg-zinc-800 hover:bg-zinc-700 text-zinc-300 border border-zinc-700 transition-colors">
                    📝 Register Page
                </a>

                <button
                    wire:click="nukeDatabase"
                    wire:loading.attr="disabled"
                    wire:confirm="⚠️ This will DELETE ALL DATA. Are you absolutely sure?"
                    class="ml-auto px-4 py-2 rounded-lg text-sm font-medium bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 transition-colors disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="nukeDatabase">🗑 Nuke Database</span>
                    <span wire:loading wire:target="nukeDatabase">Clearing…</span>
                </button>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════════ --}}
        {{-- OVERVIEW TAB --}}
        {{-- ════════════════════════════════════════════════════════════════════ --}}
        @if ($activeTab === 'overview')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Account Type Breakdown --}}
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
                    <div class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">Account Type Distribution</div>
                    @if (empty($this->accountTypeBreakdown))
                        <p class="text-zinc-600 text-sm">No accounts yet.</p>
                    @else
                        @php $total = array_sum($this->accountTypeBreakdown); @endphp
                        <div class="space-y-3">
                            @foreach ([
                                ['asset',     'bg-blue-500',   'text-blue-300'],
                                ['liability', 'bg-red-500',    'text-red-300'],
                                ['equity',    'bg-violet-500', 'text-violet-300'],
                                ['revenue',   'bg-green-500',  'text-green-300'],
                                ['expense',   'bg-amber-500',  'text-amber-300'],
                            ] as [$type, $barColor, $textColor])
                                @php $count = $this->accountTypeBreakdown[$type] ?? 0; @endphp
                                @if ($count > 0)
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="{{ $textColor }} font-medium capitalize">{{ $type }}</span>
                                            <span class="text-zinc-500">{{ $count }} / {{ $total }} ({{ round($count/$total*100) }}%)</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-zinc-800 overflow-hidden">
                                            <div class="h-full rounded-full {{ $barColor }}" style="width: {{ round($count/$total*100) }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Transaction Volume (last 30 days) --}}
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
                    <div class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">Transaction Volume — Last 30 Days</div>
                    @if (empty($this->txVolumeByDay))
                        <p class="text-zinc-600 text-sm">No transactions in last 30 days.</p>
                    @else
                        @php
                            $maxVal = collect($this->txVolumeByDay)->max(fn($r) => max((int)$r->credits, (int)$r->debits)) ?: 1;
                        @endphp
                        <div class="flex items-end gap-1 h-24">
                            @foreach ($this->txVolumeByDay as $day => $row)
                                <div class="flex-1 flex items-end gap-px" title="{{ $day }}">
                                    <div class="flex-1 rounded-sm bg-green-500/60 transition-all"
                                         style="height: {{ max(2, round(($row->credits / $maxVal) * 96)) }}px"
                                         title="Credits: ${{ number_format($row->credits/100,2) }}"></div>
                                    <div class="flex-1 rounded-sm bg-red-500/60 transition-all"
                                         style="height: {{ max(2, round(($row->debits / $maxVal) * 96)) }}px"
                                         title="Debits: ${{ number_format($row->debits/100,2) }}"></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex justify-between mt-2 text-xs text-zinc-600">
                            <span>{{ array_key_first($this->txVolumeByDay) }}</span>
                            <div class="flex items-center gap-4">
                                <span class="flex items-center gap-1"><span class="size-2 rounded-sm bg-green-500/60 inline-block"></span>Credits</span>
                                <span class="flex items-center gap-1"><span class="size-2 rounded-sm bg-red-500/60 inline-block"></span>Debits</span>
                            </div>
                            <span>{{ array_key_last($this->txVolumeByDay) }}</span>
                        </div>
                    @endif
                </div>

                {{-- Recent Transactions --}}
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5 lg:col-span-2">
                    <div class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">Recent Activity (last 10)</div>
                    @if ($this->transactions->isEmpty())
                        <p class="text-zinc-600 text-sm">No transactions yet.</p>
                    @else
                        <div class="space-y-2">
                            @foreach ($this->transactions->take(10) as $tx)
                                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-zinc-800/50 transition-colors">
                                    <span class="text-lg shrink-0">{{ $tx->type === TransactionType::Credit ? '↑' : '↓' }}</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-zinc-200 truncate">{{ $tx->description }}</div>
                                        <div class="text-xs text-zinc-500">
                                            {{ $tx->account?->organization?->name ?? '—' }} → {{ $tx->account?->name ?? '—' }}
                                            · {{ $tx->transacted_at->format('Y-m-d') }}
                                        </div>
                                    </div>
                                    <div class="font-mono text-sm font-semibold shrink-0 {{ $tx->type === TransactionType::Credit ? 'text-green-400' : 'text-red-400' }}">
                                        {{ $tx->type === TransactionType::Credit ? '+' : '-' }}${{ number_format($tx->amount_minor_units / 100, 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        {{-- ════════════════════════════════════════════════════════════════════ --}}
        {{-- ORGANIZATIONS TAB --}}
        @elseif ($activeTab === 'organizations')

            @if ($this->organizations->isEmpty())
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-12 text-center">
                    <div class="text-4xl mb-3">🏢</div>
                    <p class="text-zinc-400 font-medium">No organizations yet</p>
                    <p class="text-zinc-600 text-sm mt-1">Use "Seed Demo Data" to create some.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($this->organizations as $org)
                        <div class="rounded-xl border border-zinc-800 bg-zinc-900 overflow-hidden">
                            {{-- Org Header --}}
                            <div class="px-5 py-4 border-b border-zinc-800 flex items-center gap-4">
                                <div class="size-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($org->name, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-base font-bold text-zinc-100">{{ $org->name }}</div>
                                    <div class="text-xs text-zinc-500 font-mono">ID: {{ $org->id }} · Created {{ $org->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="flex gap-4 text-center">
                                    <div>
                                        <div class="text-lg font-bold text-indigo-300">{{ $org->memberships_count }}</div>
                                        <div class="text-xs text-zinc-600">members</div>
                                    </div>
                                    <div>
                                        <div class="text-lg font-bold text-blue-300">{{ $org->accounts_list->count() }}</div>
                                        <div class="text-xs text-zinc-600">accounts</div>
                                    </div>
                                    <div>
                                        <div class="text-lg font-bold text-cyan-300">{{ $org->accounts_list->sum('transactions_count') }}</div>
                                        <div class="text-xs text-zinc-600">transactions</div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-zinc-800">
                                {{-- Members --}}
                                <div class="p-4">
                                    <div class="text-xs font-semibold uppercase tracking-widest text-zinc-600 mb-3">Members</div>
                                    <div class="space-y-2">
                                        @foreach ($org->memberships as $membership)
                                            <div class="flex items-center gap-2.5">
                                                <div class="size-7 rounded-full bg-gradient-to-br from-violet-500/40 to-indigo-600/40 flex items-center justify-center text-xs font-bold text-violet-300 shrink-0">
                                                    {{ strtoupper(substr($membership->user?->name ?? '?', 0, 1)) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-medium text-zinc-200 truncate">{{ $membership->user?->name ?? '(deleted)' }}</div>
                                                    <div class="text-xs text-zinc-600 truncate">{{ $membership->user?->email }}</div>
                                                </div>
                                                <span class="text-xs px-1.5 py-0.5 rounded font-mono
                                                    {{ $membership->role === OrganizationRole::Owner
                                                        ? 'bg-amber-500/20 text-amber-300'
                                                        : 'bg-zinc-700 text-zinc-400' }}">
                                                    {{ $membership->role->value }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Accounts --}}
                                <div class="p-4">
                                    <div class="text-xs font-semibold uppercase tracking-widest text-zinc-600 mb-3">Accounts</div>
                                    @if ($org->accounts_list->isEmpty())
                                        <p class="text-zinc-600 text-xs">No accounts.</p>
                                    @else
                                        <div class="space-y-2">
                                            @foreach ($org->accounts_list as $acct)
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs px-1.5 py-0.5 rounded font-mono bg-zinc-800 text-zinc-400 capitalize">{{ $acct->type->value }}</span>
                                                    <div class="flex-1 min-w-0">
                                                        <a href="{{ route('accounts.show', $acct) }}"
                                                           class="text-sm font-medium text-blue-400 hover:text-blue-300 truncate block">
                                                            {{ $acct->name }}
                                                        </a>
                                                        <span class="text-xs text-zinc-600">{{ $acct->transactions_count }} transactions</span>
                                                    </div>
                                                    <div class="font-mono text-sm font-semibold shrink-0 {{ $acct->balance() >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                                        ${{ number_format($acct->balance() / 100, 2) }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        {{-- ════════════════════════════════════════════════════════════════════ --}}
        {{-- ACCOUNTS TAB --}}
        @elseif ($activeTab === 'accounts')

            @if ($this->accounts->isEmpty())
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-12 text-center">
                    <div class="text-4xl mb-3">💳</div>
                    <p class="text-zinc-400 font-medium">No accounts yet</p>
                </div>
            @else
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 overflow-hidden">
                    <div class="px-5 py-3 border-b border-zinc-800 text-xs font-semibold uppercase tracking-widest text-zinc-500">
                        All Accounts ({{ $this->accounts->count() }})
                    </div>
                    <table class="w-full text-sm">
                        <thead class="border-b border-zinc-800 text-left">
                            <tr class="text-xs text-zinc-500 uppercase tracking-wider">
                                <th class="px-5 py-3 font-medium">ID</th>
                                <th class="px-5 py-3 font-medium">Name</th>
                                <th class="px-5 py-3 font-medium">Organization</th>
                                <th class="px-5 py-3 font-medium">Type</th>
                                <th class="px-5 py-3 font-medium">Currency</th>
                                <th class="px-5 py-3 font-medium">Transactions</th>
                                <th class="px-5 py-3 font-medium text-right">Balance</th>
                                <th class="px-5 py-3 font-medium">Created</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800">
                            @foreach ($this->accounts as $acct)
                                <tr class="hover:bg-zinc-800/40 transition-colors">
                                    <td class="px-5 py-3 font-mono text-xs text-zinc-600">{{ $acct->id }}</td>
                                    <td class="px-5 py-3 font-medium text-zinc-200">{{ $acct->name }}</td>
                                    <td class="px-5 py-3 text-indigo-400 text-xs">{{ $acct->organization?->name ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium capitalize
                                            @php echo match($acct->type) {
                                                AccountType::Asset     => 'bg-blue-500/15 text-blue-300',
                                                AccountType::Liability => 'bg-red-500/15 text-red-300',
                                                AccountType::Equity    => 'bg-violet-500/15 text-violet-300',
                                                AccountType::Revenue   => 'bg-green-500/15 text-green-300',
                                                AccountType::Expense   => 'bg-amber-500/15 text-amber-300',
                                            }; @endphp">
                                            {{ $acct->type->label() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 font-mono text-xs text-zinc-400">{{ $acct->currency }}</td>
                                    <td class="px-5 py-3 text-center font-mono text-zinc-400">{{ $acct->transactions_count }}</td>
                                    <td class="px-5 py-3 text-right font-mono font-semibold {{ $acct->balance() >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                        ${{ number_format($acct->balance() / 100, 2) }}
                                    </td>
                                    <td class="px-5 py-3 text-xs text-zinc-600 font-mono">{{ $acct->created_at->format('Y-m-d') }}</td>
                                    <td class="px-5 py-3">
                                        <a href="{{ route('accounts.show', $acct) }}"
                                           class="text-xs text-blue-400 hover:text-blue-300">View →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-zinc-700">
                            <tr class="text-xs text-zinc-500">
                                <td colspan="6" class="px-5 py-3 font-medium">Totals</td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-zinc-200">
                                    ${{ number_format($this->accounts->sum(fn($a) => $a->balance()) / 100, 2) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif

        {{-- ════════════════════════════════════════════════════════════════════ --}}
        {{-- TRANSACTIONS TAB --}}
        @elseif ($activeTab === 'transactions')

            <div class="rounded-xl border border-zinc-800 bg-zinc-900 overflow-hidden">
                <div class="px-5 py-3 border-b border-zinc-800 flex items-center gap-3">
                    <div class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
                        Transactions (showing top 100)
                    </div>
                    <div class="ml-auto">
                        <input
                            wire:model.live.debounce.300ms="txSearch"
                            type="text"
                            placeholder="Search description…"
                            class="bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-1.5 text-sm text-zinc-200 placeholder-zinc-600 focus:outline-none focus:border-violet-500 w-56"
                        />
                    </div>
                </div>

                @if ($this->transactions->isEmpty())
                    <div class="p-12 text-center">
                        <p class="text-zinc-600">{{ $txSearch ? 'No results for "' . $txSearch . '"' : 'No transactions yet.' }}</p>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead class="border-b border-zinc-800 text-left">
                            <tr class="text-xs text-zinc-500 uppercase tracking-wider">
                                <th class="px-5 py-3 font-medium">ID</th>
                                <th class="px-5 py-3 font-medium">Date</th>
                                <th class="px-5 py-3 font-medium">Description</th>
                                <th class="px-5 py-3 font-medium">Account</th>
                                <th class="px-5 py-3 font-medium">Org</th>
                                <th class="px-5 py-3 font-medium">Type</th>
                                <th class="px-5 py-3 font-medium text-right">Amount</th>
                                <th class="px-5 py-3 font-medium">Recorded</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800">
                            @foreach ($this->transactions as $tx)
                                <tr class="hover:bg-zinc-800/40 transition-colors">
                                    <td class="px-5 py-2.5 font-mono text-xs text-zinc-600">{{ $tx->id }}</td>
                                    <td class="px-5 py-2.5 font-mono text-xs text-zinc-400">{{ $tx->transacted_at->format('Y-m-d') }}</td>
                                    <td class="px-5 py-2.5 text-zinc-200 max-w-xs truncate">{{ $tx->description }}</td>
                                    <td class="px-5 py-2.5 text-blue-400 text-xs">
                                        @if ($tx->account)
                                            <a href="{{ route('accounts.show', $tx->account) }}" class="hover:text-blue-300">{{ $tx->account->name }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-5 py-2.5 text-indigo-400 text-xs">{{ $tx->account?->organization?->name ?? '—' }}</td>
                                    <td class="px-5 py-2.5">
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                            {{ $tx->type === TransactionType::Credit ? 'bg-green-500/15 text-green-300' : 'bg-red-500/15 text-red-300' }}">
                                            {{ ucfirst($tx->type->value) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-2.5 text-right font-mono font-semibold {{ $tx->type === TransactionType::Credit ? 'text-green-400' : 'text-red-400' }}">
                                        {{ $tx->type === TransactionType::Credit ? '+' : '-' }}${{ number_format($tx->amount_minor_units / 100, 2) }}
                                    </td>
                                    <td class="px-5 py-2.5 font-mono text-xs text-zinc-600">{{ $tx->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-5 py-3 border-t border-zinc-800 text-xs text-zinc-600">
                        Showing {{ $this->transactions->count() }} transactions
                        @if ($txSearch) matching "{{ $txSearch }}" @endif
                    </div>
                @endif
            </div>

        {{-- ════════════════════════════════════════════════════════════════════ --}}
        {{-- USERS TAB --}}
        @elseif ($activeTab === 'users')

            @if ($this->users->isEmpty())
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-12 text-center">
                    <div class="text-4xl mb-3">👤</div>
                    <p class="text-zinc-400 font-medium">No users yet</p>
                </div>
            @else
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 overflow-hidden">
                    <div class="px-5 py-3 border-b border-zinc-800 text-xs font-semibold uppercase tracking-widest text-zinc-500">
                        All Users ({{ $this->users->count() }})
                    </div>
                    <table class="w-full text-sm">
                        <thead class="border-b border-zinc-800 text-left">
                            <tr class="text-xs text-zinc-500 uppercase tracking-wider">
                                <th class="px-5 py-3 font-medium">ID</th>
                                <th class="px-5 py-3 font-medium">Name</th>
                                <th class="px-5 py-3 font-medium">Email</th>
                                <th class="px-5 py-3 font-medium">Current Org</th>
                                <th class="px-5 py-3 font-medium">Member Of</th>
                                <th class="px-5 py-3 font-medium">Email Verified</th>
                                <th class="px-5 py-3 font-medium">2FA</th>
                                <th class="px-5 py-3 font-medium">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800">
                            @foreach ($this->users as $user)
                                <tr class="hover:bg-zinc-800/40 transition-colors">
                                    <td class="px-5 py-3 font-mono text-xs text-zinc-600">{{ $user->id }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="size-7 rounded-full bg-gradient-to-br from-violet-500/40 to-indigo-600/40 flex items-center justify-center text-xs font-bold text-violet-300 shrink-0">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <span class="font-medium text-zinc-200">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 font-mono text-xs text-zinc-400">{{ $user->email }}</td>
                                    <td class="px-5 py-3 text-indigo-400 text-xs">{{ $user->currentOrganization?->name ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($user->organizations as $org)
                                                <span class="text-xs px-1.5 py-0.5 rounded bg-zinc-800 text-zinc-400">{{ $org->name }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($user->email_verified_at)
                                            <span class="text-xs text-green-400 font-mono">✓ {{ $user->email_verified_at->format('Y-m-d') }}</span>
                                        @else
                                            <span class="text-xs text-red-400">✗ Not verified</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($user->two_factor_confirmed_at)
                                            <span class="text-xs text-green-400">✓ On</span>
                                        @else
                                            <span class="text-xs text-zinc-600">Off</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 font-mono text-xs text-zinc-600">{{ $user->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Login hints --}}
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
                    <div class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-3">Demo Credentials (seeded users)</div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach ([
                            ['alice@demo.test', 'Alice Founder', 'Owner — Acme Corp'],
                            ['bob@demo.test',   'Bob Accountant', 'Member — Acme Corp'],
                            ['carol@demo.test', 'Carol CFO', 'Owner — Globex LLC'],
                        ] as [$email, $name, $role])
                            <div class="rounded-lg bg-zinc-800 border border-zinc-700 p-3">
                                <div class="text-sm font-medium text-zinc-200 mb-0.5">{{ $name }}</div>
                                <div class="font-mono text-xs text-zinc-400">{{ $email }}</div>
                                <div class="text-xs text-violet-400 mt-1">{{ $role }}</div>
                                <div class="text-xs text-zinc-600 mt-1 font-mono">password: <span class="text-zinc-400">password</span></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        {{-- ════════════════════════════════════════════════════════════════════ --}}
        {{-- SYSTEM TAB --}}
        @elseif ($activeTab === 'system')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Environment --}}
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
                    <div class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">Environment</div>
                    <div class="space-y-3">
                        @foreach ($this->systemInfo as $key => $value)
                            <div class="flex justify-between items-center py-1.5 border-b border-zinc-800 last:border-0">
                                <span class="text-xs text-zinc-500 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                <span class="font-mono text-xs {{ $value === 'enabled' ? 'text-amber-400' : ($value === 'disabled' ? 'text-zinc-400' : 'text-zinc-200') }}">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- DB Row Counts --}}
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
                    <div class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">Database Counts</div>
                    <div class="space-y-3">
                        @foreach ([
                            ['users', '👤'],
                            ['organizations', '🏢'],
                            ['organization_memberships', '🤝'],
                            ['accounts', '💳'],
                            ['transactions', '↕'],
                        ] as [$table, $icon])
                            <div class="flex justify-between items-center py-1.5 border-b border-zinc-800 last:border-0">
                                <span class="text-xs text-zinc-500 font-mono">{{ $icon }} {{ $table }}</span>
                                <span class="font-mono text-sm text-zinc-200 font-bold">{{ DB::table($table)->count() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Routes --}}
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5 md:col-span-2">
                    <div class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">Application Routes</div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs font-mono">
                            <thead class="border-b border-zinc-800">
                                <tr class="text-zinc-600 uppercase">
                                    <th class="pr-4 pb-2 text-left">Method</th>
                                    <th class="pr-4 pb-2 text-left">URI</th>
                                    <th class="pr-4 pb-2 text-left">Name</th>
                                    <th class="pb-2 text-left">Middleware</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/50">
                                @foreach (collect(app('router')->getRoutes())->filter(fn($r) => !str_starts_with($r->uri(), 'livewire') && !str_starts_with($r->uri(), 'flux') && !str_starts_with($r->uri(), '_') && !str_starts_with($r->uri(), 'storage') && $r->uri() !== 'up') as $route)
                                    <tr class="hover:bg-zinc-800/30 transition-colors">
                                        <td class="pr-4 py-1.5">
                                            @foreach (array_filter($route->methods(), fn($m) => $m !== 'HEAD') as $method)
                                                <span class="inline-block px-1.5 py-0.5 rounded text-xs font-bold mr-1
                                                    {{ match($method) {
                                                        'GET'    => 'bg-blue-500/20 text-blue-300',
                                                        'POST'   => 'bg-green-500/20 text-green-300',
                                                        'PUT','PATCH' => 'bg-amber-500/20 text-amber-300',
                                                        'DELETE' => 'bg-red-500/20 text-red-300',
                                                        default  => 'bg-zinc-700 text-zinc-400',
                                                    } }}">{{ $method }}</span>
                                            @endforeach
                                        </td>
                                        <td class="pr-4 py-1.5 text-zinc-300">/{{ $route->uri() }}</td>
                                        <td class="pr-4 py-1.5 text-violet-400">{{ $route->getName() ?? '' }}</td>
                                        <td class="py-1.5 text-zinc-600">{{ implode(', ', $route->gatherMiddleware()) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        @endif

    </main>

    {{-- Footer --}}
    <footer class="mt-12 border-t border-zinc-800 px-6 py-4 text-center text-xs text-zinc-700 font-mono">
        LedgerFlow Dev Dashboard · {{ now()->format('Y-m-d H:i:s T') }} · PHP {{ PHP_VERSION }} · Laravel {{ app()->version() }}
    </footer>

</div>
