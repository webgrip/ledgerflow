<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WebhookStatus;
use Database\Factories\WebhookEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $provider
 * @property string $idempotency_key
 * @property string $event_type
 * @property array<string, mixed> $payload
 * @property WebhookStatus $status
 * @property string|null $failure_reason
 * @property int|null $organization_id
 */
#[Fillable(['provider', 'idempotency_key', 'event_type', 'payload', 'status', 'failure_reason', 'organization_id'])]
class WebhookEvent extends Model
{
    /** @use HasFactory<WebhookEventFactory> */
    use HasFactory;

    protected $table = 'provider_events';

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'status' => WebhookStatus::class,
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function isPending(): bool
    {
        return $this->status === WebhookStatus::Pending;
    }

    public function markProcessing(): void
    {
        $this->update(['status' => WebhookStatus::Processing]);
    }

    public function markProcessed(): void
    {
        $this->update(['status' => WebhookStatus::Processed]);
    }

    public function markFailed(string $reason): void
    {
        $this->update(['status' => WebhookStatus::Failed, 'failure_reason' => $reason]);
    }
}
