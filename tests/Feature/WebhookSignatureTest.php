<?php

declare(strict_types=1);

/**
 * Webhook Signature Validation Tests
 *
 * Tests Stripe HMAC-SHA256 signature verification and the fallback
 * behavior when no secret is configured (local/demo mode).
 */

use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

describe('Stripe signature validation', function () {
    function stripeSignatureHeader(string $payload, string $secret, ?int $timestamp = null): string
    {
        $t = $timestamp ?? time();
        $signed = "{$t}.{$payload}";
        $sig = hash_hmac('sha256', $signed, $secret);

        return "t={$t},v1={$sig}";
    }

    it('accepts a valid Stripe signature', function () {
        Queue::fake();
        $secret = 'whsec_test123';
        Config::set('webhooks.secrets.stripe', $secret);

        $payload = json_encode(['id' => 'evt_sig_001', 'type' => 'payment_intent.succeeded']);
        $header = stripeSignatureHeader($payload, $secret);

        $this->call('POST', '/webhooks/stripe', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $header,
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(202);

        expect(WebhookEvent::count())->toBe(1);
    });

    it('rejects a tampered Stripe payload', function () {
        Queue::fake();
        $secret = 'whsec_test123';
        Config::set('webhooks.secrets.stripe', $secret);

        $originalPayload = json_encode(['id' => 'evt_sig_002', 'type' => 'charge.created']);
        $tamperedPayload = json_encode(['id' => 'evt_sig_002', 'type' => 'charge.created', 'tampered' => true]);
        $header = stripeSignatureHeader($originalPayload, $secret);

        $this->call('POST', '/webhooks/stripe', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $header,
            'CONTENT_TYPE' => 'application/json',
        ], $tamperedPayload)->assertStatus(401);

        expect(WebhookEvent::count())->toBe(0);
        Queue::assertNothingPushed();
    });

    it('rejects a request with a wrong secret', function () {
        Queue::fake();
        Config::set('webhooks.secrets.stripe', 'correct_secret');

        $payload = json_encode(['id' => 'evt_sig_003', 'type' => 'charge.created']);
        $header = stripeSignatureHeader($payload, 'wrong_secret');

        $this->call('POST', '/webhooks/stripe', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $header,
            'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(401);

        expect(WebhookEvent::count())->toBe(0);
    });

    it('rejects a request with no Stripe-Signature header when secret is configured', function () {
        Queue::fake();
        Config::set('webhooks.secrets.stripe', 'whsec_configured');

        $this->postJson('/webhooks/stripe', [
            'id' => 'evt_no_header',
            'type' => 'charge.created',
        ])->assertStatus(401);

        expect(WebhookEvent::count())->toBe(0);
        Queue::assertNothingPushed();
    });
});

describe('Skip validation when no secret is configured', function () {
    it('accepts webhook without signature when no secret is set', function () {
        Queue::fake();
        Config::set('webhooks.secrets.stripe', '');

        $this->postJson('/webhooks/stripe', [
            'id' => 'evt_no_secret',
            'type' => 'payment_intent.succeeded',
        ])->assertStatus(202);

        expect(WebhookEvent::count())->toBe(1);
    });
});
