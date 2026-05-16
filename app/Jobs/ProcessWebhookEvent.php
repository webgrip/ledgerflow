<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\WebhookStatus;
use App\Models\WebhookEvent;
use App\Services\AuditLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class ProcessWebhookEvent implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly int $webhookEventId,
    ) {}

    public function handle(): void
    {
        $event = WebhookEvent::find($this->webhookEventId);

        if ($event === null) {
            return;
        }

        // Idempotency guard — skip if already processed
        if ($event->status === WebhookStatus::Processed) {
            return;
        }

        $event->markProcessing();

        try {
            // Provider-specific dispatch could be added here
            // For now, log and mark processed
            AuditLogger::log(
                event: 'webhook.processed',
                subject: $event,
                organizationId: $event->organization_id,
                metadata: [
                    'provider' => $event->provider,
                    'event_type' => $event->event_type,
                    'idempotency_key' => $event->idempotency_key,
                ],
            );

            $event->markProcessed();
        } catch (\Throwable $e) {
            $event->markFailed($e->getMessage());
            throw $e;
        }
    }
}
