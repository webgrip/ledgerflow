<?php

use App\Actions\RecordTransaction;
use App\Enums\TransactionType;
use App\Models\Account;
use Carbon\CarbonImmutable;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Record Transaction')] class extends Component {
    public Account $account;

    #[Validate('required|string|in:debit,credit')]
    public string $type = 'credit';

    #[Validate('required|numeric|min:0.01|max:9999999.99')]
    public string $amount = '';

    #[Validate('required|string|min:2|max:255')]
    public string $description = '';

    #[Validate('required|date')]
    public string $transactedAt = '';

    public function mount(Account $account): void
    {
        $this->authorize('view', $account);
        $this->account = $account;
        $this->transactedAt = now()->format('Y-m-d');
    }

    public function save(RecordTransaction $action): void
    {
        $this->validate();

        $amountMinorUnits = (int) round(floatval($this->amount) * 100);

        $action->handle(
            account: $this->account,
            type: TransactionType::from($this->type),
            amountMinorUnits: $amountMinorUnits,
            description: $this->description,
            transactedAt: CarbonImmutable::parse($this->transactedAt),
        );

        Flux::toast(variant: 'success', text: __('Transaction recorded.'));

        $this->redirect(route('accounts.show', $this->account), navigate: true);
    }
}; ?>

<div class="max-w-lg py-8">
    <div class="flex items-center gap-4 mb-6">
        <flux:button variant="ghost" size="sm" :href="route('accounts.show', $account)" wire:navigate icon="arrow-left">
            {{ $account->name }}
        </flux:button>
    </div>

    <flux:heading size="xl" class="mb-1">{{ __('Record Transaction') }}</flux:heading>
    <flux:subheading class="mb-6">{{ __('Add a transaction to :name.', ['name' => $account->name]) }}</flux:subheading>

    <form wire:submit="save" class="space-y-6">
        <flux:select wire:model="type" :label="__('Type')" required>
            <flux:select.option value="credit">{{ __('Credit') }}</flux:select.option>
            <flux:select.option value="debit">{{ __('Debit') }}</flux:select.option>
        </flux:select>

        <flux:input
            wire:model="amount"
            :label="__('Amount (:currency)', ['currency' => $account->currency])"
            type="number"
            step="0.01"
            min="0.01"
            required
            placeholder="0.00"
        />

        <flux:input
            wire:model="description"
            :label="__('Description')"
            type="text"
            required
            autofocus
        />

        <flux:input
            wire:model="transactedAt"
            :label="__('Date')"
            type="date"
            required
        />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('Record') }}</flux:button>
            <flux:button variant="ghost" :href="route('accounts.show', $account)" wire:navigate>{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
