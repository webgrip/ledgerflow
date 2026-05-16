<?php

use App\Actions\RunReconciliation;
use App\Enums\ReconciliationStatus;
use App\Models\Organization;
use App\Models\ReconciliationRun;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Reconciliation')] class extends Component {
    public string $periodStart = '';
    public string $periodEnd = '';
    public bool $running = false;

    public function mount(): void
    {
        $this->periodStart = now()->startOfMonth()->toDateString();
        $this->periodEnd = now()->endOfMonth()->toDateString();
    }

    #[Computed]
    public function runs(): \Illuminate\Database\Eloquent\Collection
    {
        return ReconciliationRun::where('organization_id', Auth::user()->current_organization_id)
            ->with(['initiator'])
            ->withCount(['issues as open_issues_count' => fn ($q) => $q->where('status', 'open')])
            ->latest()
            ->get();
    }

    public function startRun(): void
    {
        $this->authorize('create', ReconciliationRun::class);

        $org = Auth::user()->currentOrganization;
        abort_if($org === null, 403, 'No organization selected.');

        $run = app(RunReconciliation::class)->handle(
            organization: $org,
            periodStart: CarbonImmutable::parse($this->periodStart),
            periodEnd: CarbonImmutable::parse($this->periodEnd),
            initiator: Auth::user(),
        );

        $this->dispatch('$refresh');

        session()->flash('success', "Reconciliation run completed. {$run->unmatched_count} issue(s) found.");
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ __('Reconciliation') }}</flux:heading>
            <flux:subheading>{{ __('Review and resolve financial discrepancies.') }}</flux:subheading>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- Start a new run --}}
    <div class="mb-6 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
        <flux:heading size="lg" class="mb-4">{{ __('New Reconciliation Run') }}</flux:heading>
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Period Start') }}</label>
                <flux:input wire:model="periodStart" type="date" size="sm" />
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Period End') }}</label>
                <flux:input wire:model="periodEnd" type="date" size="sm" />
            </div>
            <flux:button wire:click="startRun" wire:loading.attr="disabled" variant="primary">
                <span wire:loading.remove wire:target="startRun">▶ {{ __('Run Reconciliation') }}</span>
                <span wire:loading wire:target="startRun">{{ __('Running…') }}</span>
            </flux:button>
        </div>
    </div>

    {{-- Runs list --}}
    @if ($this->runs->isEmpty())
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-12 text-center">
            <flux:heading>{{ __('No reconciliation runs yet') }}</flux:heading>
            <flux:subheading>{{ __('Start your first run above to detect discrepancies.') }}</flux:subheading>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('Period') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-4 py-3 font-medium text-right">{{ __('Matched') }}</th>
                        <th class="px-4 py-3 font-medium text-right">{{ __('Issues') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Run By') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($this->runs as $run)
                        <tr wire:key="{{ $run->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-4 py-3 font-mono text-xs text-zinc-700 dark:text-zinc-300">
                                {{ $run->period_start->format('Y-m-d') }} → {{ $run->period_end->format('Y-m-d') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ match($run->status) {
                                        \App\Enums\ReconciliationStatus::Completed => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        \App\Enums\ReconciliationStatus::Running => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                        \App\Enums\ReconciliationStatus::Failed => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        default => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
                                    } }}">
                                    {{ ucfirst($run->status->value) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-zinc-700 dark:text-zinc-300">{{ $run->matched_count }}</td>
                            <td class="px-4 py-3 text-right">
                                @if ($run->open_issues_count > 0)
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                        {{ $run->open_issues_count }} open
                                    </span>
                                @else
                                    <span class="text-zinc-400 text-xs">None</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400 text-xs">{{ $run->initiator?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-zinc-500 text-xs">{{ $run->created_at->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('reconciliation.show', $run) }}" wire:navigate
                                   class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ __('View') }} →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
