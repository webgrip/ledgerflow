<?php

use App\Actions\CreateOrganization;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Create Organization')] class extends Component {
    #[Validate('required|string|min:2|max:255')]
    public string $name = '';

    public function create(CreateOrganization $action): void
    {
        $this->validate();

        $action->handle(Auth::user(), $this->name);

        Flux::toast(variant: 'success', text: __('Organization created.'));

        $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div class="max-w-lg py-8">
    <flux:heading size="xl" class="mb-1">{{ __('Create Organization') }}</flux:heading>
    <flux:subheading class="mb-6">{{ __('Set up a new organization workspace.') }}</flux:subheading>

    <form wire:submit="create" class="space-y-6">
        <flux:input
            wire:model="name"
            :label="__('Organization Name')"
            type="text"
            required
            autofocus
            autocomplete="off"
            :placeholder="__('e.g. Acme Corp')"
        />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">
                {{ __('Create Organization') }}
            </flux:button>
            <flux:button variant="ghost" :href="route('dashboard')" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</div>
