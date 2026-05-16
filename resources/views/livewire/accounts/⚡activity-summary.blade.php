<?php

use App\Ai\Agents\AccountActivitySummarizer;
use App\Models\Account;
use Livewire\Component;

new class extends Component {
    public Account $account;
    public ?string $summary = null;
    public bool $loading = false;
    public ?string $error = null;

    public function summarize(): void
    {
        $this->authorize('view', $this->account);

        $this->loading = true;
        $this->summary = null;
        $this->error = null;

        try {
            $response = (new AccountActivitySummarizer($this->account))
                ->prompt('Summarize recent account activity.');
            $this->summary = $response->text;
        } catch (\Throwable) {
            $this->error = __('AI summary is temporarily unavailable.');
        } finally {
            $this->loading = false;
        }
    }

    public function dismiss(): void
    {
        $this->summary = null;
        $this->error = null;
    }
}; ?>

<div class="mb-4">
    @if ($this->summary)
        <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800">
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-start gap-2">
                    <flux:icon name="sparkles" class="size-4 text-blue-500 shrink-0 mt-1" />
                    <div>
                        <div class="text-xs font-semibold text-blue-700 dark:text-blue-300 mb-1">{{ __('AI Activity Summary') }}</div>
                        <p class="text-sm text-blue-900 dark:text-blue-100">{{ $this->summary }}</p>
                    </div>
                </div>
                <flux:button wire:click="dismiss" variant="ghost" size="xs" icon="x-mark" />
            </div>
        </div>
    @elseif ($this->error)
        <div class="p-3 rounded-lg bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-300">
            {{ $this->error }}
        </div>
    @else
        <flux:button wire:click="summarize" wire:loading.attr="disabled" variant="ghost" size="sm" icon="sparkles">
            <span wire:loading.remove wire:target="summarize">{{ __('AI Summary') }}</span>
            <span wire:loading wire:target="summarize">{{ __('Summarizing…') }}</span>
        </flux:button>
    @endif
</div>
