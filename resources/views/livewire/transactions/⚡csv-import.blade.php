<?php

use App\Actions\RecordTransaction;
use App\Enums\TransactionType;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * CSV Import component for bulk transaction import.
 *
 * Expected CSV format (with header row):
 *   Date,Description,Type,Amount
 *   2024-01-15,Invoice from ACME,credit,150.00
 *   2024-01-20,Office supplies,debit,45.50
 *
 * - Date: YYYY-MM-DD
 * - Type: credit or debit (case-insensitive)
 * - Amount: decimal with up to 2 decimal places (EUR cents = amount * 100)
 */
new class extends Component {
    use WithFileUploads;

    public Account $account;
    public mixed $file = null;

    /** @var array<int, array<string, mixed>> */
    public array $preview = [];

    /** @var array<string, string> */
    public array $errors = [];

    public bool $importing = false;
    public int $importedCount = 0;
    public int $skippedCount = 0;

    #[Computed]
    public function hasPreview(): bool
    {
        return count($this->preview) > 0;
    }

    public function updatedFile(): void
    {
        $this->preview = [];
        $this->errors = [];
        $this->importedCount = 0;
        $this->skippedCount = 0;

        if ($this->file === null) {
            return;
        }

        $this->validate([
            'file' => 'file|mimes:csv,txt|max:2048',
        ]);

        $rows = $this->parseFile();
        $this->preview = array_slice($rows['valid'], 0, 5);
        $this->errors = $rows['errors'];
    }

    public function import(): void
    {
        $this->authorize('create', \App\Models\Transaction::class);

        $this->importing = true;
        $rows = $this->parseFile();
        $seen = [];

        foreach ($rows['valid'] as $row) {
            $key = "{$row['date']}|{$row['description']}|{$row['type']}|{$row['amount_minor']}";

            if (in_array($key, $seen, true)) {
                $this->skippedCount++;
                continue;
            }

            $seen[] = $key;

            app(RecordTransaction::class)->handle(
                account: $this->account,
                type: TransactionType::from(strtolower($row['type'])),
                amountMinorUnits: $row['amount_minor'],
                description: $row['description'],
                transactedAt: \Carbon\CarbonImmutable::parse($row['date']),
                actor: Auth::user(),
            );

            $this->importedCount++;
        }

        $this->importing = false;
        $this->file = null;
        $this->preview = [];

        $this->dispatch('import-complete', imported: $this->importedCount, skipped: $this->skippedCount);
    }

    /**
     * @return array{valid: array<int, array<string, mixed>>, errors: array<string, string>}
     */
    private function parseFile(): array
    {
        $valid = [];
        $errors = [];
        $path = $this->file?->getRealPath();

        if ($path === false || $path === null || ! file_exists($path)) {
            return ['valid' => [], 'errors' => ['file' => 'Could not read uploaded file.']];
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            return ['valid' => [], 'errors' => ['file' => 'Could not open file.']];
        }

        $headers = null;
        $lineNumber = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if ($headers === null) {
                $headers = array_map('strtolower', array_map('trim', $row));
                continue;
            }

            if (count($row) < 4) {
                $errors["line_{$lineNumber}"] = "Line {$lineNumber}: insufficient columns.";
                continue;
            }

            $mapped = array_combine($headers, array_map('trim', $row));

            if ($mapped === false) {
                $errors["line_{$lineNumber}"] = "Line {$lineNumber}: column mismatch.";
                continue;
            }

            $date = $mapped['date'] ?? '';
            $description = $mapped['description'] ?? '';
            $type = strtolower($mapped['type'] ?? '');
            $amount = $mapped['amount'] ?? '';

            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $errors["line_{$lineNumber}"] = "Line {$lineNumber}: invalid date '{$date}'.";
                continue;
            }

            if (! in_array($type, ['credit', 'debit'], true)) {
                $errors["line_{$lineNumber}"] = "Line {$lineNumber}: type must be 'credit' or 'debit'.";
                continue;
            }

            if (! is_numeric($amount) || (float) $amount <= 0) {
                $errors["line_{$lineNumber}"] = "Line {$lineNumber}: invalid amount '{$amount}'.";
                continue;
            }

            $valid[] = [
                'date' => $date,
                'description' => $description,
                'type' => $type,
                'amount_minor' => (int) round((float) $amount * 100),
                'amount_display' => number_format((float) $amount, 2),
            ];
        }

        fclose($handle);

        return ['valid' => $valid, 'errors' => $errors];
    }
}; ?>

<div>
    <flux:modal name="csv-import" class="max-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Import Transactions') }}</flux:heading>
                <flux:subheading>
                    {{ __('Upload a CSV file with columns: Date, Description, Type (credit/debit), Amount') }}
                </flux:subheading>
            </div>

            <flux:field>
                <flux:label>{{ __('CSV File') }}</flux:label>
                <input
                    type="file"
                    wire:model="file"
                    accept=".csv,.txt"
                    class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200"
                />
                <flux:error name="file" />
            </flux:field>

            @if(count($this->errors) > 0)
                <flux:callout variant="warning" icon="exclamation-triangle">
                    <flux:callout.heading>{{ __('Validation issues') }}</flux:callout.heading>
                    <ul class="text-sm space-y-1 mt-1">
                        @foreach($this->errors as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </flux:callout>
            @endif

            @if($this->hasPreview)
                <div>
                    <flux:subheading class="mb-2">{{ __('Preview (first 5 rows)') }}</flux:subheading>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Date') }}</flux:table.column>
                            <flux:table.column>{{ __('Description') }}</flux:table.column>
                            <flux:table.column>{{ __('Type') }}</flux:table.column>
                            <flux:table.column>{{ __('Amount') }}</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($this->preview as $row)
                                <flux:table.row>
                                    <flux:table.cell class="text-sm font-mono">{{ $row['date'] }}</flux:table.cell>
                                    <flux:table.cell class="text-sm">{{ $row['description'] }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge :color="$row['type'] === 'credit' ? 'green' : 'red'" size="sm">
                                            {{ ucfirst($row['type']) }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell class="text-sm font-mono">€{{ $row['amount_display'] }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif

            @if($importedCount > 0 || $skippedCount > 0)
                <flux:callout variant="success" icon="check-circle">
                    {{ __('Imported :count transaction(s).', ['count' => $importedCount]) }}
                    @if($skippedCount > 0)
                        {{ __(':n duplicate(s) skipped.', ['n' => $skippedCount]) }}
                    @endif
                </flux:callout>
            @endif

            <div class="flex gap-3 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button
                    wire:click="import"
                    wire:loading.attr="disabled"
                    :disabled="!$this->hasPreview || $importing"
                    variant="primary"
                    icon="arrow-up-tray"
                >
                    <span wire:loading.remove>{{ __('Import') }}</span>
                    <span wire:loading>{{ __('Importing…') }}</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
