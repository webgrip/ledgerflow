<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Responses\TextResponse;

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

    /**
     * Record an AI agent call with usage metadata for cost tracking.
     *
     * @param  array<string, mixed>  $extra
     */
    public static function logAiCall(
        string $agentClass,
        TextResponse $response,
        ?Model $subject = null,
        ?int $organizationId = null,
        array $extra = [],
    ): AuditEvent {
        $usage = $response->usage ?? null;

        $metadata = array_merge([
            'agent' => class_basename($agentClass),
            'model' => $response->model ?? 'unknown',
            'input_tokens' => $usage?->promptTokens,
            'output_tokens' => $usage?->completionTokens,
        ], $extra);

        return self::log(
            event: 'ai.agent_called',
            subject: $subject,
            organizationId: $organizationId,
            metadata: array_filter($metadata, fn ($v) => $v !== null),
        );
    }
}
