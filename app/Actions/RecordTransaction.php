<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use Carbon\CarbonImmutable;

class RecordTransaction
{
    public function handle(
        Account $account,
        TransactionType $type,
        int $amountMinorUnits,
        string $description,
        ?CarbonImmutable $transactedAt = null,
    ): Transaction {
        return Transaction::create([
            'account_id' => $account->id,
            'type' => $type,
            'amount_minor_units' => $amountMinorUnits,
            'description' => $description,
            'transacted_at' => $transactedAt ?? CarbonImmutable::now(),
        ]);
    }
}
