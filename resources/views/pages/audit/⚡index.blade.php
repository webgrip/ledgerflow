<?php

use App\Models\AuditEvent;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Audit Log')] class extends Component {
    use WithPagination;

    #[Url(as: 'event')]
    public string $filterEvent = '';

    #[Url(as: 'actor')]
    public string $filterActor = '';

    #[Computed]
    public function events(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return AuditEvent::where('organization_id', Auth::user()->current_organization_id)
            ->with(['actor'])
            ->when($this->filterEvent !== '', fn ($q) => $q->where('event', $this->filterEvent))
            ->when($this->filterActor !== '', fn ($q) => $q->whereHas('actor', fn ($q) => $q->where('name', 'ilike', "%{$this->filterActor}%")))
            ->latest()
            ->paginate(25);
    }

    #[Computed]
    public function distinctEvents(): array
    {
        return AuditEvent::where('organization_id', Auth::user()->current_organization_id)
            ->distinct()
            ->pluck('event')
            ->sort()
            ->values()
            ->toArray();
    }

    public function clearFilters(): void
    {
        $this->filterEvent = '';
        $this->filterActor = '';
        $this->resetPage();
    }

    public function updatedFilterEvent(): void
    {
        $this->resetPage();
    }

    public function updatedFilterActor(): void
    {
        $this->resetPage();
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ __('Audit Log') }}</flux:heading>
            <flux:subheading>{{ __('A complete record of all actions taken in this organization.') }}</flux:subheading>
        </div>
    </div>

    {{-- Filters --}}
    <flux:card class="mb-6 p-4">
        <div class="flex flex-wrap gap-4 items-end">
            <flux:select
                wire:model.live="filterEvent"
                label="{{ __('Event type') }}"
                placeholder="{{ __('All events') }}"
                class="min-w-48"
            >
                @foreach($this->distinctEvents as $event)
                    <flux:select.option value="{{ $event }}">{{ str($event)->replace('.', ' ')->title() }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input
                wire:model.live.debounce.300ms="filterActor"
                label="{{ __('Actor name') }}"
                placeholder="{{ __('Filter by name…') }}"
            />

            @if($filterEvent || $filterActor)
                <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                    {{ __('Clear') }}
                </flux:button>
            @endif
        </div>
    </flux:card>

    {{-- Results --}}
    <flux:card>
        @if($this->events->isEmpty())
            <div class="py-12 text-center text-zinc-400">
                {{ __('No audit events found.') }}
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('When') }}</flux:table.column>
                    <flux:table.column>{{ __('Actor') }}</flux:table.column>
                    <flux:table.column>{{ __('Event') }}</flux:table.column>
                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                    <flux:table.column>{{ __('Details') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($this->events as $event)
                        <flux:table.row>
                            <flux:table.cell class="text-sm text-zinc-500 whitespace-nowrap">
                                <span title="{{ $event->created_at->toDateTimeString() }}">
                                    {{ $event->created_at->diffForHumans() }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell class="font-medium">
                                {{ $event->actor?->name ?? __('System') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge variant="outline" size="sm">
                                    {{ str($event->event)->replace('.', ' ')->title() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="text-sm text-zinc-600">
                                @if($event->subject_type)
                                    {{ class_basename($event->subject_type) }}
                                    @if($event->subject_id)
                                        <span class="text-zinc-400">#{{ $event->subject_id }}</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="text-xs text-zinc-400 max-w-xs truncate">
                                @if($event->metadata)
                                    {{ collect($event->metadata)->map(fn($v, $k) => "{$k}: {$v}")->implode(', ') }}
                                @else
                                    —
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="mt-4 px-2">
                {{ $this->events->links() }}
            </div>
        @endif
    </flux:card>
</div>
