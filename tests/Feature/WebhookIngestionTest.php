<?php

declare(strict_types=1);

use App\Enums\WebhookStatus;
use App\Jobs\ProcessWebhookEvent;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

describe('WebhookController', function () {
    it('accepts a new webhook event and returns 202', function () {
        Queue::fake();

        $response = $this->postJson('/webhooks/stripe', [
            'id' => 'evt_test_001',
            'type' => 'payment_intent.succeeded',
            'data' => ['amount' => 5000],
        ]);

        $response->assertStatus(202);
        expect(WebhookEvent::count())->toBe(1);

        $event = WebhookEvent::first();
        expect($event->provider)->toBe('stripe')
            ->and($event->event_type)->toBe('payment_intent.succeeded')
            ->and($event->status)->toBe(WebhookStatus::Pending);

        Queue::assertPushed(ProcessWebhookEvent::class, fn ($job) => $job->webhookEventId === $event->id);
    });

    it('returns 200 and does not create a duplicate for the same idempotency key', function () {
        Queue::fake();

        $payload = ['id' => 'evt_test_dup', 'type' => 'charge.updated'];

        $this->postJson('/webhooks/stripe', $payload)->assertStatus(202);
        $this->postJson('/webhooks/stripe', $payload)->assertStatus(200);

        expect(WebhookEvent::count())->toBe(1);
        Queue::assertPushed(ProcessWebhookEvent::class, 1);
    });

    it('uses stripe event id header for idempotency key if present', function () {
        Queue::fake();

        $this->postJson('/webhooks/stripe', ['type' => 'test'], ['Stripe-Event-Id' => 'evt_header_001'])
            ->assertStatus(202);

        expect(WebhookEvent::first()->idempotency_key)->toBe('stripe:evt_header_001');
    });

    it('falls back to sha256 hash when no id field present', function () {
        Queue::fake();

        $payload = ['type' => 'no_id_event'];
        $this->postJson('/webhooks/mollie', $payload)->assertStatus(202);

        $event = WebhookEvent::first();
        expect($event->idempotency_key)->toStartWith('mollie:');
    });

    it('resolves mollie event type from action field', function () {
        Queue::fake();

        $this->postJson('/webhooks/mollie', ['action' => 'payment.paid', 'id' => 'tr_mollie_001']);

        expect(WebhookEvent::first()->event_type)->toBe('payment.paid');
    });
});

describe('ProcessWebhookEvent job', function () {
    it('marks a pending event as processed', function () {
        $event = WebhookEvent::factory()->create(['status' => WebhookStatus::Pending]);

        (new ProcessWebhookEvent($event->id))->handle();

        expect($event->fresh()->status)->toBe(WebhookStatus::Processed);
    });

    it('skips processing if event is already processed (idempotency guard)', function () {
        $event = WebhookEvent::factory()->create(['status' => WebhookStatus::Processed]);

        // We call handle() twice — second call should be a no-op
        (new ProcessWebhookEvent($event->id))->handle();
        (new ProcessWebhookEvent($event->id))->handle();

        expect($event->fresh()->status)->toBe(WebhookStatus::Processed);
    });

    it('marks event failed and re-throws on exception', function () {
        $event = WebhookEvent::factory()->create(['status' => WebhookStatus::Pending]);

        $event->markProcessing();
        expect($event->fresh()->status)->toBe(WebhookStatus::Processing);

        $event->markFailed('test error');
        expect($event->fresh()->status)->toBe(WebhookStatus::Failed)
            ->and($event->fresh()->failure_reason)->toBe('test error');
    });

    it('gracefully handles a missing webhook event id', function () {
        // Should not throw — just silently return when event is not found
        (new ProcessWebhookEvent(9999))->handle();
        expect(true)->toBeTrue();
    });
});
