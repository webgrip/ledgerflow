<?php

use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Accounts')] class extends Component {
    public function mount(): void
    {
        abort_unless(Auth::user()->current_organization_id !== null, 403, 'No organization selected.');
    }

    #[Computed]
    public function accounts(): \Illuminate\Database\Eloquent\Collection
    {
        $this->authorize('viewAny', Account::class);

        return Account::where('organization_id', Auth::user()->current_organization_id)
            ->orderBy('name')
            ->get();
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">{{ __('Accounts') }}</flux:heading>
        <a href="{{ route('accounts.create') }}" wire:navigate
           class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-zinc-900 hover:bg-zinc-700 dark:hover:bg-zinc-100">
            + {{ __('New Account') }}
        </a>
    </div>

    @if ($this->accounts->isEmpty())
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-12 text-center">
            <flux:heading>{{ __('No accounts yet') }}</flux:heading>
            <flux:subheading>{{ __('Create your first account to get started.') }}</flux:subheading>
            <a href="{{ route('accounts.create') }}" wire:navigate
               class="mt-4 inline-flex items-center gap-2 rounded-lg bg-zinc-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-zinc-900">
                {{ __('Create Account') }}
            </a>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Type') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Currency') }}</th>
                        <th class="px-4 py-3 font-medium text-right">{{ __('Balance') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($this->accounts as $account)
                        <tr wire:key="{{ $account->id }}"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 cursor-pointer"
                            onclick="window.location='{{ route('accounts.show', $account) }}'">
                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $account->name }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $account->type->label() }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $account->currency }}</td>
                            <td class="px-4 py-3 font-mono text-right text-zinc-900 dark:text-zinc-100">
                                {{ number_format($account->balance() / 100, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
