<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\Transaction;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[UseCheapestModel]
class TransactionExplainer implements Agent
{
    use Promptable;

    public function __construct(
        private readonly Transaction $transaction,
    ) {}

    public function instructions(): Stringable|string
    {
        $account = $this->transaction->account;

        return <<<INSTRUCTIONS
        You are a financial assistant that explains transactions in plain language to non-expert users.

        Context:
        - Account: {$account->name} ({$account->type->label()}, {$account->currency})
        - Transaction type: {$this->transaction->type->value}
        - Amount: {$account->currency} {$this->formatAmount($this->transaction->amount_minor_units)}
        - Description: {$this->transaction->description}
        - Date: {$this->transaction->transacted_at->format('Y-m-d')}

        Provide a concise, friendly 1-2 sentence explanation of what this transaction means.
        Always include an advisory disclaimer that you are an AI and the user should verify details with their accountant.
        Do not invent information not present in the context above.
        INSTRUCTIONS;
    }

    private function formatAmount(int $minorUnits): string
    {
        return number_format($minorUnits / 100, 2);
    }
}
