<?php

use App\Models\Account;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Account')] class extends Component {
    public Account $account;

    public function mount(Account $account): void
    {
        $this->authorize('view', $account);
    }

    #[Computed]
    public function transactions(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->account->transactions()->orderByDesc('transacted_at')->get();
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ $account->name }}</flux:heading>
            <flux:subheading>{{ $account->type->label() }} · {{ $account->currency }}</flux:subheading>
        </div>
        <div class="text-right">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Balance') }}</div>
            <div class="text-2xl font-mono font-semibold text-zinc-900 dark:text-zinc-100">
                {{ number_format($account->balance() / 100, 2) }} {{ $account->currency }}
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between mb-4">
        <flux:heading size="lg">{{ __('Transactions') }}</flux:heading>
        <a href="{{ route('transactions.create', $account) }}" wire:navigate
           class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-zinc-900 hover:bg-zinc-700 dark:hover:bg-zinc-100">
            + {{ __('Record Transaction') }}
        </a>
    </div>

    @if ($this->transactions->isEmpty())
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-12 text-center">
            <flux:heading>{{ __('No transactions yet') }}</flux:heading>
            <flux:subheading>{{ __('Record your first transaction for this account.') }}</flux:subheading>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Description') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Type') }}</th>
                        <th class="px-4 py-3 font-medium text-right">{{ __('Amount') }}</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($this->transactions as $transaction)
                        <tr wire:key="{{ $transaction->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $transaction->transacted_at->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $transaction->description }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $transaction->type->value === 'credit' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                    {{ ucfirst($transaction->type->value) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-right text-zinc-900 dark:text-zinc-100">
                                {{ number_format($transaction->amount_minor_units / 100, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                @livewire('transactions.explain-button', ['transaction' => $transaction], key($transaction->id))
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
