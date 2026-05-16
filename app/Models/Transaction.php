<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['account_id', 'type', 'amount_minor_units', 'description', 'transacted_at'])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount_minor_units' => 'integer',
            'transacted_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
