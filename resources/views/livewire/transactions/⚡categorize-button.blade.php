<?php

use App\Ai\Agents\TransactionCategorizer;
use App\Models\Transaction;
use App\Services\AuditLogger;
use Livewire\Component;

new class extends Component {
    public Transaction $transaction;
    public ?string $category = null;
    public ?string $reason = null;
    public bool $loading = false;
    public ?string $error = null;

    public function categorize(): void
    {
        $this->authorize('view', $this->transaction->account);

        $this->loading = true;
        $this->category = null;
        $this->error = null;

        try {
            $response = (new TransactionCategorizer($this->transaction))
                ->prompt('Categorize this transaction.');

            $this->category = $response['category'] ?? 'other';
            $this->reason = $response['reason'] ?? null;

            AuditLogger::log(
                event: 'ai.transaction_categorized',
                subject: $this->transaction,
                organizationId: $this->transaction->account->organization_id,
                metadata: ['category' => $this->category],
            );
        } catch (\Throwable) {
            $this->error = __('Categorization unavailable.');
        } finally {
            $this->loading = false;
        }
    }

    public function dismiss(): void
    {
        $this->category = null;
        $this->reason = null;
        $this->error = null;
    }
}; ?>

<div>
    @if ($this->category)
        <div class="flex items-center gap-1">
            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400"
                  title="{{ $this->reason }}">
                {{ str_replace('_', ' ', $this->category) }}
            </span>
            <flux:button wire:click="dismiss" variant="ghost" size="xs" icon="x-mark" />
        </div>
    @elseif ($this->error)
        <span class="text-xs text-red-500">{{ $this->error }}</span>
    @else
        <flux:button
            wire:click="categorize"
            wire:loading.attr="disabled"
            variant="ghost"
            size="xs"
            icon="tag"
        >
            <span wire:loading.remove wire:target="categorize">{{ __('Categorize') }}</span>
            <span wire:loading wire:target="categorize">…</span>
        </flux:button>
    @endif
</div>
