<?php

use App\Enums\WebhookStatus;
use App\Jobs\ProcessWebhookEvent;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Webhook Events')] class extends Component {
    use WithPagination;

    #[Url(as: 'status')]
    public string $filterStatus = '';

    #[Url(as: 'provider')]
    public string $filterProvider = '';

    #[Computed]
    public function events(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return WebhookEvent::where('organization_id', Auth::user()->current_organization_id)
            ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterProvider !== '', fn ($q) => $q->where('provider', $this->filterProvider))
            ->latest()
            ->paginate(25);
    }

    #[Computed]
    public function statusOptions(): array
    {
        return array_map(fn ($s) => $s->value, WebhookStatus::cases());
    }

    public function replay(int $eventId): void
    {
        $org = Auth::user()->currentOrganization;
        abort_if($org === null || ! $org->ownerOf(Auth::user()), 403);

        $event = WebhookEvent::findOrFail($eventId);
        abort_unless($event->organization_id === $org->id, 403);

        $event->update(['status' => WebhookStatus::Pending, 'failure_reason' => null]);
        ProcessWebhookEvent::dispatch($event->id);

        session()->flash('success', "Event #{$eventId} re-queued for processing.");
    }

    public function clearFilters(): void
    {
        $this->filterStatus = '';
        $this->filterProvider = '';
        $this->resetPage();
    }

    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterProvider(): void { $this->resetPage(); }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ __('Webhook Events') }}</flux:heading>
            <flux:subheading>{{ __('Incoming provider events and their processing status.') }}</flux:subheading>
        </div>
    </div>

    @if(session('success'))
        <flux:callout variant="success" icon="check-circle" class="mb-4">
            {{ session('success') }}
        </flux:callout>
    @endif

    {{-- Filters --}}
    <flux:card class="mb-6 p-4">
        <div class="flex flex-wrap gap-4 items-end">
            <flux:select wire:model.live="filterStatus" label="{{ __('Status') }}" placeholder="{{ __('All statuses') }}" class="min-w-36">
                @foreach($this->statusOptions as $status)
                    <flux:select.option value="{{ $status }}">{{ ucfirst($status) }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model.live.debounce.300ms="filterProvider" label="{{ __('Provider') }}" placeholder="stripe, mollie…" />

            @if($filterStatus || $filterProvider)
                <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">{{ __('Clear') }}</flux:button>
            @endif
        </div>
    </flux:card>

    <flux:card>
        @if($this->events->isEmpty())
            <div class="py-12 text-center text-zinc-400">{{ __('No webhook events found.') }}</div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Received') }}</flux:table.column>
                    <flux:table.column>{{ __('Provider') }}</flux:table.column>
                    <flux:table.column>{{ __('Event type') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($this->events as $event)
                        <flux:table.row>
                            <flux:table.cell class="text-sm text-zinc-500 whitespace-nowrap">
                                <span title="{{ $event->created_at->toDateTimeString() }}">
                                    {{ $event->created_at->diffForHumans() }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell class="font-medium capitalize">{{ $event->provider }}</flux:table.cell>
                            <flux:table.cell class="text-sm font-mono">{{ $event->event_type }}</flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $color = match($event->status) {
                                        \App\Enums\WebhookStatus::Processed => 'green',
                                        \App\Enums\WebhookStatus::Failed => 'red',
                                        \App\Enums\WebhookStatus::Processing => 'yellow',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge :color="$color" size="sm">
                                    {{ ucfirst($event->status->value) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($event->status === \App\Enums\WebhookStatus::Failed)
                                    <flux:button
                                        wire:click="replay({{ $event->id }})"
                                        wire:confirm="{{ __('Re-queue this event for processing?') }}"
                                        variant="ghost"
                                        size="sm"
                                        icon="arrow-path"
                                    >
                                        {{ __('Replay') }}
                                    </flux:button>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="mt-4 px-2">{{ $this->events->links() }}</div>
        @endif
    </flux:card>
</div>
