<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Record an audit event.
     *
     * @param  array<string, mixed>  $metadata
     */
    public static function log(
        string $event,
        ?Model $subject = null,
        ?int $organizationId = null,
        ?int $userId = null,
        array $metadata = [],
    ): AuditEvent {
        return AuditEvent::create([
            'event' => $event,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'organization_id' => $organizationId ?? Auth::user()?->current_organization_id,
            'user_id' => $userId ?? Auth::id(),
            'metadata' => empty($metadata) ? null : $metadata,
        ]);
    }
}
