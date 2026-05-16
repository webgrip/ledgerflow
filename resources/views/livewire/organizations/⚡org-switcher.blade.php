<?php

use App\Actions\SwitchOrganization;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public function switchTo(int $organizationId, SwitchOrganization $action): void
    {
        $organization = Organization::findOrFail($organizationId);

        $action->handle(Auth::user(), $organization);

        $this->redirect(route('dashboard'), navigate: true);
    }

    #[Computed]
    public function organizations(): \Illuminate\Database\Eloquent\Collection
    {
        return Auth::user()->organizations()->orderBy('name')->get();
    }

    #[Computed]
    public function currentOrganization(): ?Organization
    {
        return Auth::user()->currentOrganization;
    }
}; ?>

<flux:dropdown>
    <flux:sidebar.item
        icon="building-office-2"
        icon-trailing="chevron-up-down"
        class="cursor-pointer"
    >
        {{ $this->currentOrganization?->name ?? __('Select Organization') }}
    </flux:sidebar.item>

    <flux:menu>
        @forelse ($this->organizations as $organization)
            <flux:menu.item
                wire:click="switchTo({{ $organization->id }})"
                :icon="$this->currentOrganization?->id === $organization->id ? 'check' : ''"
            >
                {{ $organization->name }}
            </flux:menu.item>
        @empty
            <flux:menu.item disabled>
                {{ __('No organizations') }}
            </flux:menu.item>
        @endforelse

        <flux:menu.separator />

        <flux:menu.item icon="plus" :href="route('organizations.create')" wire:navigate>
            {{ __('New Organization') }}
        </flux:menu.item>
    </flux:menu>
</flux:dropdown>
