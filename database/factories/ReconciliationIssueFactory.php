<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReconciliationIssueStatus;
use App\Enums\ReconciliationIssueType;
use App\Models\Organization;
use App\Models\ReconciliationIssue;
use App\Models\ReconciliationRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReconciliationIssue>
 */
class ReconciliationIssueFactory extends Factory
{
    protected $model = ReconciliationIssue::class;

    public function definition(): array
    {
        return [
            'reconciliation_run_id' => ReconciliationRun::factory(),
            'organization_id' => Organization::factory(),
            'issue_type' => $this->faker->randomElement(ReconciliationIssueType::cases()),
            'status' => ReconciliationIssueStatus::Open,
            'details' => [
                'provider' => 'stripe',
                'event_type' => 'payment.succeeded',
                'amount' => $this->faker->numberBetween(100, 100000),
            ],
        ];
    }

    public function resolved(): static
    {
        return $this->state(['status' => ReconciliationIssueStatus::Resolved]);
    }
}
