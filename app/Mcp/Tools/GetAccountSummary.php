<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Account;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Get a summary of financial accounts for the current organization, including balances and recent activity counts.')]
class GetAccountSummary extends Tool
{
    public function handle(Request $request): Response
    {
        $user = Auth::user();
        $orgId = $user?->current_organization_id;

        if ($orgId === null) {
            return Response::text('No organization selected for this user.');
        }

        $accounts = Account::where('organization_id', $orgId)
            ->withCount('transactions')
            ->get();

        if ($accounts->isEmpty()) {
            return Response::text('No accounts found for the current organization.');
        }

        $lines = $accounts->map(function (Account $account): string {
            $balance = $account->balance();
            $currency = $account->currency;
            $type = $account->type->label();
            $txCount = $account->transactions_count;

            return "- {$account->name} | {$type} | {$currency} | Balance: {$balance} | Transactions: {$txCount}";
        })->join("\n");

        return Response::text("Accounts for organization #{$orgId}:\n{$lines}");
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
