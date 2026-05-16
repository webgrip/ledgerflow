<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Search transactions across all accounts in the current organization. Supports filtering by description, type (credit/debit), date range, and amount range.')]
class SearchTransactions extends Tool
{
    public function handle(Request $request): Response
    {
        $user = Auth::user();
        $orgId = $user?->current_organization_id;

        if ($orgId === null) {
            return Response::text('No organization selected for this user.');
        }

        $query = Transaction::whereHas(
            'account',
            fn ($q) => $q->where('organization_id', $orgId)
        )->with('account');

        if ($search = $request->get('search')) {
            $query->where('description', 'ilike', "%{$search}%");
        }

        if ($type = $request->get('type')) {
            $typeEnum = TransactionType::tryFrom($type);
            if ($typeEnum !== null) {
                $query->where('type', $typeEnum);
            }
        }

        if ($from = $request->get('date_from')) {
            $query->whereDate('transacted_at', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->whereDate('transacted_at', '<=', $to);
        }

        $limit = min((int) ($request->get('limit') ?? 20), 100);

        $transactions = $query->orderByDesc('transacted_at')->limit($limit)->get();

        if ($transactions->isEmpty()) {
            return Response::text('No transactions found matching the given criteria.');
        }

        $lines = $transactions->map(fn (Transaction $t): string => sprintf(
            '- [%s] %s | %s | %d %s | Account: %s',
            $t->transacted_at->format('Y-m-d'),
            $t->type->value,
            $t->description,
            $t->amount_minor_units,
            $t->account->currency,
            $t->account->name,
        ))->join("\n");

        return Response::text("Found {$transactions->count()} transaction(s):\n{$lines}");
    }

    /**  array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Filter by description (partial match)'),
            'type' => $schema->string()->description('Transaction type: credit or debit'),
            'date_from' => $schema->string()->description('Start date (YYYY-MM-DD)'),
            'date_to' => $schema->string()->description('End date (YYYY-MM-DD)'),
            'limit' => $schema->integer()->description('Max results to return (default 20, max 100)'),
        ];
    }
}
