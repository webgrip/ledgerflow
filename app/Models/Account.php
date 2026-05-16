<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\TransactionType;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property AccountType $type
 * @property string $currency
 * @property string $name
 * @property int $organization_id
 */
#[Fillable(['organization_id', 'name', 'type', 'currency', 'description'])]
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return HasMany<Transaction, $this> */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Balance in minor units (credits minus debits).
     */
    public function balance(): int
    {
        $credits = $this->transactions()
            ->where('type', TransactionType::Credit)
            ->sum('amount_minor_units');

        $debits = $this->transactions()
            ->where('type', TransactionType::Debit)
            ->sum('amount_minor_units');

        return (int) ($credits - $debits);
    }
}
