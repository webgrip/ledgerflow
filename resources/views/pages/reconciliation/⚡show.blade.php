<?php

use App\Ai\Agents\ReconciliationAnalyst;
use App\Enums\ReconciliationIssueStatus;
use App\Models\ReconciliationIssue;
use App\Models\ReconciliationRun;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Reconciliation Run')] class extends Component {
    public ReconciliationRun $run;

    public function mount(ReconciliationRun $run): void
    {
        abort_unless(
            $run->organization_id === Auth::user()->current_organization_id,
            403,
        );
    }

    #[Computed]
    public function issues(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->run->issues()->with(['resolver'])->latest()->get();
    }

    public function resolve(int $issueId): void
    {
        $issue = ReconciliationIssue::findOrFail($issueId);
        abort_unless($issue->run->organization_id === Auth::user()->current_organization_id, 403);

        $issue->update([
            'status' => ReconciliationIssueStatus::Resolved,
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
        ]);

        AuditLogger::log(
            event: 'reconciliation.issue_resolved',
            subject: $issue,
            organizationId: $issue->organization_id,
        );
    }

    public function ignore(int $issueId): void
    {
        $issue = ReconciliationIssue::findOrFail($issueId);
        abort_unless($issue->run->organization_id === Auth::user()->current_organization_id, 403);

        $issue->update(['status' => ReconciliationIssueStatus::Ignored]);
    }

    public function explainIssue(int $issueId): void
    {
        $issue = ReconciliationIssue::findOrFail($issueId);
        abort_unless($issue->run->organization_id === Auth::user()->current_organization_id, 403);

        try {
            $response = (new ReconciliationAnalyst($issue))->prompt('Please explain this reconciliation issue.');

            $issue->update(['ai_explanation' => $response->text]);

            AuditLogger::log(
                event: 'ai.reconciliation_explained',
                subject: $issue,
                organizationId: $issue->organization_id,
            );
        } catch (\Throwable) {
            session()->flash('error', __('AI explanation temporarily unavailable.'));
        }

        $this->dispatch('$refresh');
    }
}; ?>

<div>
    <div class="flex items-center gap-3 mb-2">
        <a href="{{ route('reconciliation.index') }}" wire:navigate class="text-sm text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300">
            ← {{ __('Reconciliation') }}
        </a>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ __('Run #:id', ['id' => $run->id]) }}</flux:heading>
            <flux:subheading>
                {{ $run->period_start->format('Y-m-d') }} → {{ $run->period_end->format('Y-m-d') }}
                · {{ ucfirst($run->status->value) }}
                · {{ $run->matched_count }} matched, {{ $run->unmatched_count }} issues
            </flux:subheading>
        </div>
    </div>

    @if ($this->issues->isEmpty())
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-12 text-center">
            <flux:heading>{{ __('No issues found') }}</flux:heading>
            <flux:subheading>{{ __('This reconciliation run found no discrepancies.') }}</flux:subheading>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($this->issues as $issue)
                <div wire:key="{{ $issue->id }}" class="rounded-xl border {{ $issue->isOpen() ? 'border-amber-200 dark:border-amber-800' : 'border-zinc-200 dark:border-zinc-700' }} p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $issue->isOpen() ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                    {{ ucfirst($issue->status->value) }}
                                </span>
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $issue->issue_type->label() }}
                                </span>
                            </div>

                            <div class="text-xs font-mono text-zinc-500 bg-zinc-50 dark:bg-zinc-900 rounded p-2 mb-3 max-w-lg overflow-x-auto">
                                {{ json_encode($issue->details, JSON_PRETTY_PRINT) }}
                            </div>

                            @if ($issue->ai_explanation)
                                <div class="mt-2 p-3 rounded-lg bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 text-sm max-w-lg">
                                    <div class="flex items-start gap-2">
                                        <flux:icon name="sparkles" class="size-4 text-blue-500 shrink-0 mt-0.5" />
                                        <p class="text-blue-900 dark:text-blue-100">{{ $issue->ai_explanation }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-2 shrink-0">
                            @if ($issue->isOpen())
                                <flux:button wire:click="explainIssue({{ $issue->id }})" wire:loading.attr="disabled" variant="ghost" size="xs" icon="sparkles">
                                    <span wire:loading.remove wire:target="explainIssue({{ $issue->id }})">{{ __('Explain') }}</span>
                                    <span wire:loading wire:target="explainIssue({{ $issue->id }})">{{ __('Thinking…') }}</span>
                                </flux:button>
                                <flux:button wire:click="resolve({{ $issue->id }})" variant="ghost" size="xs" icon="check">
                                    {{ __('Resolve') }}
                                </flux:button>
                                <flux:button wire:click="ignore({{ $issue->id }})" variant="ghost" size="xs" icon="eye-slash">
                                    {{ __('Ignore') }}
                                </flux:button>
                            @else
                                <span class="text-xs text-zinc-500">
                                    {{ $issue->resolver?->name ?? __('—') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
