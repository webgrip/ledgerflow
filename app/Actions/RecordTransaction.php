<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuditLogger;
use Carbon\CarbonImmutable;

class RecordTransaction
{
    public function handle(
        Account $account,
        TransactionType $type,
        int $amountMinorUnits,
        string $description,
        ?CarbonImmutable $transactedAt = null,
        ?User $actor = null,
    ): Transaction {
        $transaction = Transaction::create([
            'account_id' => $account->id,
            'type' => $type,
            'amount_minor_units' => $amountMinorUnits,
            'description' => $description,
            'transacted_at' => $transactedAt ?? CarbonImmutable::now(),
        ]);

        AuditLogger::log(
            event: 'transaction.recorded',
            subject: $transaction,
            organizationId: $account->organization_id,
            userId: $actor?->id,
            metadata: [
                'account_id' => $account->id,
                'type' => $type->value,
                'amount_minor_units' => $amountMinorUnits,
            ],
        );

        return $transaction;
    }
}
