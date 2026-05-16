<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Enums\TransactionType;
use App\Models\Account;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[UseCheapestModel]
class AccountActivitySummarizer implements Agent
{
    use Promptable;

    public function __construct(
        private readonly Account $account,
    ) {}

    public function instructions(): Stringable|string
    {
        $transactions = $this->account->transactions()
            ->orderByDesc('transacted_at')
            ->limit(20)
            ->get();

        $count = $transactions->count();
        $credits = $transactions->where('type', TransactionType::Credit)->sum('amount_minor_units');
        $debits = $transactions->where('type', TransactionType::Debit)->sum('amount_minor_units');
        $balance = $this->account->balance();

        $txLines = $transactions->map(fn ($t) => "- {$t->transacted_at->format('Y-m-d')} | {$t->type->value} | {$t->description} | {$t->amount_minor_units} minor units"
        )->join("\n");

        return <<<INSTRUCTIONS
        You are a financial assistant providing account activity summaries to non-expert users.

        Account: {$this->account->name} ({$this->account->type->label()}, {$this->account->currency})
        Current balance: {$balance} minor units
        Recent activity ({$count} transactions, last 20):
        Total credits: {$credits} minor units
        Total debits: {$debits} minor units

        Transactions:
        {$txLines}

        Provide a 3-5 sentence plain-language summary of this account's recent activity.
        Highlight notable patterns, large transactions, or anything the user should be aware of.
        End with a disclaimer that you are an AI and figures should be verified.
        INSTRUCTIONS;
    }
}
