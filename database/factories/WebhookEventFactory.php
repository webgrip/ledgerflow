<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WebhookStatus;
use App\Models\WebhookEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WebhookEvent>
 */
class WebhookEventFactory extends Factory
{
    protected $model = WebhookEvent::class;

    public function definition(): array
    {
        return [
            'provider' => $this->faker->randomElement(['stripe', 'mollie', 'adyen']),
            'idempotency_key' => 'stripe:'.Str::uuid(),
            'event_type' => $this->faker->randomElement(['payment.succeeded', 'payment.failed', 'refund.created']),
            'payload' => [
                'id' => 'evt_'.Str::random(12),
                'type' => 'payment.succeeded',
                'amount' => $this->faker->numberBetween(100, 100000),
            ],
            'status' => WebhookStatus::Pending,
        ];
    }

    public function processed(): static
    {
        return $this->state(['status' => WebhookStatus::Processed]);
    }

    public function failed(): static
    {
        return $this->state([
            'status' => WebhookStatus::Failed,
            'failure_reason' => 'Processing error',
        ]);
    }

    public function forProvider(string $provider): static
    {
        return $this->state(['provider' => $provider]);
    }
}
