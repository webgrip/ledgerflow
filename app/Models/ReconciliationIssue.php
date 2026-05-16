<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReconciliationIssueStatus;
use App\Enums\ReconciliationIssueType;
use Carbon\CarbonImmutable;
use Database\Factories\ReconciliationIssueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $reconciliation_run_id
 * @property int $organization_id
 * @property ReconciliationIssueType $issue_type
 * @property ReconciliationIssueStatus $status
 * @property array<string, mixed> $details
 * @property string|null $ai_explanation
 * @property int|null $resolved_by
 * @property CarbonImmutable|null $resolved_at
 */
#[Fillable(['reconciliation_run_id', 'organization_id', 'issue_type', 'status', 'details', 'ai_explanation', 'resolved_by', 'resolved_at'])]
class ReconciliationIssue extends Model
{
    /** @use HasFactory<ReconciliationIssueFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ReconciliationIssueStatus::class,
            'issue_type' => ReconciliationIssueType::class,
            'details' => 'array',
            'resolved_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ReconciliationRun, $this> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(ReconciliationRun::class, 'reconciliation_run_id');
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<User, $this> */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isOpen(): bool
    {
        return $this->status === ReconciliationIssueStatus::Open;
    }
}
