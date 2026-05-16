<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'type' => fake()->randomElement(TransactionType::cases()),
            'amount_minor_units' => fake()->numberBetween(100, 100000),
            'description' => fake()->sentence(4),
            'transacted_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function credit(): static
    {
        return $this->state(['type' => TransactionType::Credit]);
    }

    public function debit(): static
    {
        return $this->state(['type' => TransactionType::Debit]);
    }
}
