<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ProcessWebhookEvent;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Receive an incoming webhook from an external provider.
     *
     * Stores the raw event for idempotent queued processing.
     * Duplicate deliveries (same idempotency key) are silently accepted.
     */
    public function receive(Request $request, string $provider): Response
    {
        $payload = $request->all();
        $idempotencyKey = $this->resolveIdempotencyKey($request, $provider, $payload);
        $eventType = $this->resolveEventType($provider, $payload);

        // Idempotency: return 200 immediately if already seen
        $existing = WebhookEvent::where('idempotency_key', $idempotencyKey)->first();

        if ($existing !== null) {
            Log::debug('Webhook duplicate received', [
                'provider' => $provider,
                'idempotency_key' => $idempotencyKey,
            ]);

            return new Response('', 200);
        }

        $event = WebhookEvent::create([
            'provider' => $provider,
            'idempotency_key' => $idempotencyKey,
            'event_type' => $eventType,
            'payload' => $payload,
        ]);

        ProcessWebhookEvent::dispatch($event->id);

        return new Response('', 202);
    }

    private function resolveIdempotencyKey(Request $request, string $provider, array $payload): string
    {
        // Stripe-style
        if ($id = $request->header('Stripe-Event-Id') ?? $payload['id'] ?? null) {
            return "{$provider}:{$id}";
        }

        // Fallback: hash of provider + content
        return "{$provider}:".hash('sha256', $request->getContent());
    }

    private function resolveEventType(string $provider, array $payload): string
    {
        return match ($provider) {
            'stripe' => $payload['type'] ?? 'unknown',
            'mollie' => $payload['action'] ?? 'unknown',
            default => $payload['event'] ?? $payload['type'] ?? 'unknown',
        };
    }
}
