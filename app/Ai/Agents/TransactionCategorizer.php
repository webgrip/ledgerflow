<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\Transaction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[UseCheapestModel]
class TransactionCategorizer implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        private readonly Transaction $transaction,
    ) {}

    public function instructions(): Stringable|string
    {
        return <<<INSTRUCTIONS
        You are a financial assistant that categorizes business transactions.

        Transaction:
        - Description: {$this->transaction->description}
        - Type: {$this->transaction->type->value}
        - Amount: {$this->transaction->amount_minor_units} minor units

        Categorize this transaction into one of these categories:
        revenue, cost_of_goods, payroll, rent_utilities, marketing, software_tools,
        travel, professional_services, taxes, banking_fees, transfers, other

        Provide a category and a short reason (max 20 words).
        INSTRUCTIONS;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()->required(),
            'reason' => $schema->string()->required(),
        ];
    }
}
