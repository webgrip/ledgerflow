<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\ReconciliationIssue;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[UseCheapestModel]
class ReconciliationAnalyst implements Agent
{
    use Promptable;

    public function __construct(
        private readonly ReconciliationIssue $issue,
    ) {}

    public function instructions(): Stringable|string
    {
        $details = json_encode($this->issue->details, JSON_PRETTY_PRINT);

        return <<<INSTRUCTIONS
        You are a financial reconciliation assistant. You help non-expert users understand
        reconciliation issues in plain language.

        Context:
        - Issue type: {$this->issue->issue_type->label()}
        - Issue status: {$this->issue->status->value}
        - Details: {$details}

        Provide a concise, friendly 2-3 sentence explanation of:
        1. What this reconciliation issue means
        2. What likely caused it
        3. How to resolve it

        Always include a disclaimer that you are an AI and the user should verify with their accountant.
        Do not invent information not present in the context above.
        INSTRUCTIONS;
    }
}
