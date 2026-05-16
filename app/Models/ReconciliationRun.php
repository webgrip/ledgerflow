<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReconciliationIssueStatus;
use App\Enums\ReconciliationStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ReconciliationRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $organization_id
 * @property int|null $initiated_by
 * @property ReconciliationStatus $status
 * @property CarbonImmutable $period_start
 * @property CarbonImmutable $period_end
 * @property int $matched_count
 * @property int $unmatched_count
 * @property string|null $notes
 */
#[Fillable(['organization_id', 'initiated_by', 'status', 'period_start', 'period_end', 'matched_count', 'unmatched_count', 'notes'])]
class ReconciliationRun extends Model
{
    /** @use HasFactory<ReconciliationRunFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ReconciliationStatus::class,
            'period_start' => 'immutable_date',
            'period_end' => 'immutable_date',
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<User, $this> */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /** @return HasMany<ReconciliationIssue, $this> */
    public function issues(): HasMany
    {
        return $this->hasMany(ReconciliationIssue::class);
    }

    public function openIssuesCount(): int
    {
        return $this->issues()->where('status', ReconciliationIssueStatus::Open)->count();
    }

    public function isCompleted(): bool
    {
        return $this->status === ReconciliationStatus::Completed;
    }
}
