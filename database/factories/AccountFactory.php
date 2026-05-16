<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(3, true),
            'type' => fake()->randomElement(AccountType::cases()),
            'currency' => 'USD',
            'description' => null,
        ];
    }

    public function asset(): static
    {
        return $this->state(['type' => AccountType::Asset]);
    }

    public function liability(): static
    {
        return $this->state(['type' => AccountType::Liability]);
    }

    public function expense(): static
    {
        return $this->state(['type' => AccountType::Expense]);
    }

    public function revenue(): static
    {
        return $this->state(['type' => AccountType::Revenue]);
    }
}
