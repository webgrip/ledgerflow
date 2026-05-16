<?php

use App\Actions\CreateAccount;
use App\Enums\AccountType;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('New Account')] class extends Component {
    #[Validate('required|string|min:2|max:255')]
    public string $name = '';

    #[Validate('required|string|in:asset,liability,equity,revenue,expense')]
    public string $type = '';

    #[Validate('required|string|size:3')]
    public string $currency = 'USD';

    #[Validate('nullable|string|max:500')]
    public string $description = '';

    public function mount(): void
    {
        $this->authorize('create', \App\Models\Account::class);
    }

    public function save(CreateAccount $action): void
    {
        $this->validate();

        $org = Auth::user()->currentOrganization;
        abort_unless($org !== null, 403);

        $account = $action->handle(
            organization: $org,
            name: $this->name,
            type: AccountType::from($this->type),
            currency: strtoupper($this->currency),
            description: $this->description ?: null,
        );

        Flux::toast(variant: 'success', text: __('Account created.'));

        $this->redirect(route('accounts.show', $account), navigate: true);
    }

    /** @return array<string, string> */
    public function accountTypeOptions(): array
    {
        return collect(AccountType::cases())
            ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
            ->all();
    }
}; ?>

<div class="max-w-lg py-8">
    <flux:heading size="xl" class="mb-1">{{ __('New Account') }}</flux:heading>
    <flux:subheading class="mb-6">{{ __('Add a financial account to your organization.') }}</flux:subheading>

    <form wire:submit="save" class="space-y-6">
        <flux:input
            wire:model="name"
            :label="__('Account Name')"
            type="text"
            required
            autofocus
        />

        <flux:select wire:model="type" :label="__('Account Type')" required>
            <flux:select.option value="">{{ __('Select type…') }}</flux:select.option>
            @foreach ($this->accountTypeOptions() as $value => $label)
                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input
            wire:model="currency"
            :label="__('Currency')"
            type="text"
            maxlength="3"
            placeholder="USD"
        />

        <flux:textarea
            wire:model="description"
            :label="__('Description')"
            rows="2"
            :placeholder="__('Optional description')"
        />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('Create Account') }}</flux:button>
            <flux:button variant="ghost" :href="route('accounts.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
