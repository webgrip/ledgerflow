<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReconciliationStatus;
use App\Models\Organization;
use App\Models\ReconciliationRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReconciliationRun>
 */
class ReconciliationRunFactory extends Factory
{
    protected $model = ReconciliationRun::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-3 months', '-1 month');
        $end = $this->faker->dateTimeBetween('-1 month', 'now');

        return [
            'organization_id' => Organization::factory(),
            'initiated_by' => User::factory(),
            'status' => ReconciliationStatus::Completed,
            'period_start' => $start->format('Y-m-d'),
            'period_end' => $end->format('Y-m-d'),
            'matched_count' => $this->faker->numberBetween(0, 20),
            'unmatched_count' => $this->faker->numberBetween(0, 5),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => ReconciliationStatus::Pending, 'matched_count' => 0, 'unmatched_count' => 0]);
    }
}
