<?php

use App\Ai\Agents\TransactionExplainer;
use App\Models\Transaction;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public Transaction $transaction;

    public ?string $explanation = null;
    public bool $loading = false;
    public ?string $error = null;

    public function explain(): void
    {
        $this->authorize('view', $this->transaction->account);

        $this->loading = true;
        $this->explanation = null;
        $this->error = null;

        try {
            $response = (new TransactionExplainer($this->transaction))
                ->prompt('Please explain this transaction.');

            $this->explanation = $response->text;

            AuditLogger::logAiCall(
                agentClass: TransactionExplainer::class,
                response: $response,
                subject: $this->transaction,
                organizationId: $this->transaction->account->organization_id,
                extra: ['transaction_id' => $this->transaction->id],
            );
        } catch (\Throwable $e) {
            $this->error = __('AI explanation is temporarily unavailable. Please try again later.');
        } finally {
            $this->loading = false;
        }
    }

    public function dismiss(): void
    {
        $this->explanation = null;
        $this->error = null;
    }
}; ?>

<div>
    @if ($this->explanation)
        <div class="mt-2 p-3 rounded-lg bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 text-sm max-w-md">
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-start gap-2">
                    <flux:icon name="sparkles" class="size-4 text-blue-500 shrink-0 mt-0.5" />
                    <p class="text-blue-900 dark:text-blue-100">{{ $this->explanation }}</p>
                </div>
                <flux:button wire:click="dismiss" variant="ghost" size="xs" icon="x-mark" />
            </div>
        </div>
    @elseif ($this->error)
        <div class="mt-2 p-3 rounded-lg bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-sm max-w-md">
            <p class="text-red-700 dark:text-red-300">{{ $this->error }}</p>
        </div>
    @else
        <flux:button
            wire:click="explain"
            wire:loading.attr="disabled"
            variant="ghost"
            size="xs"
            icon="sparkles"
        >
            <span wire:loading.remove wire:target="explain">{{ __('Explain') }}</span>
            <span wire:loading wire:target="explain">{{ __('Thinking…') }}</span>
        </flux:button>
    @endif
</div>
