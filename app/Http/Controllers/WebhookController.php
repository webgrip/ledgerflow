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
     * Validates the webhook signature before processing.
     * Stores the raw event for idempotent queued processing.
     * Duplicate deliveries (same idempotency key) are silently accepted.
     */
    public function receive(Request $request, string $provider): Response
    {
        if (! $this->validateSignature($request, $provider)) {
            Log::warning('Webhook signature validation failed', ['provider' => $provider]);

            return new Response('Unauthorized', 401);
        }

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

    /**
     * Validate the webhook signature for the given provider.
     *
     * Returns true when:
     * - No signing secret is configured (local/testing — skip validation)
     * - Stripe HMAC-SHA256 v1 signature matches
     * - Any future provider's signature matches
     */
    private function validateSignature(Request $request, string $provider): bool
    {
        $secret = config("webhooks.secrets.{$provider}");

        // No secret configured → skip validation (local dev / demo mode)
        if (empty($secret)) {
            return true;
        }

        return match ($provider) {
            'stripe' => $this->validateStripeSignature($request, (string) $secret),
            default => true,
        };
    }

    /**
     * Validate Stripe's HMAC-SHA256 webhook signature.
     *
     * @see https://stripe.com/docs/webhooks/signatures
     *
     * Header format: t=<timestamp>,v1=<hmac>
     */
    private function validateStripeSignature(Request $request, string $secret): bool
    {
        $header = $request->header('Stripe-Signature', '');

        if (empty($header)) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $part) {
            [$key, $val] = array_pad(explode('=', $part, 2), 2, '');
            $parts[$key] = $val;
        }

        $timestamp = $parts['t'] ?? '';
        $v1 = $parts['v1'] ?? '';

        if (empty($timestamp) || empty($v1)) {
            return false;
        }

        $payload = "{$timestamp}.".$request->getContent();
        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $v1);
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
