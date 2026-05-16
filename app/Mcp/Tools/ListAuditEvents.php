<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\AuditEvent;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List recent audit events for the current organization. Shows what actions were performed, by whom, and when.')]
class ListAuditEvents extends Tool
{
    public function handle(Request $request): Response
    {
        $user = Auth::user();
        $orgId = $user?->current_organization_id;

        if ($orgId === null) {
            return Response::text('No organization selected for this user.');
        }

        $query = AuditEvent::where('organization_id', $orgId)->with('actor');

        if ($eventFilter = $request->get('event')) {
            $query->where('event', 'ilike', "%{$eventFilter}%");
        }

        $limit = min((int) ($request->get('limit') ?? 25), 100);

        $events = $query->orderByDesc('created_at')->limit($limit)->get();

        if ($events->isEmpty()) {
            return Response::text('No audit events found for the current organization.');
        }

        $lines = $events->map(fn (AuditEvent $e): string => sprintf(
            '- [%s] %s | By: %s | Metadata: %s',
            $e->created_at->format('Y-m-d H:i:s'),
            $e->event,
            $e->actor->name ?? 'system',
            json_encode($e->metadata),
        ))->join("\n");

        return Response::text("Found {$events->count()} audit event(s):\n{$lines}");
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'event' => $schema->string()->description('Filter by event name (partial match, e.g. "transaction", "reconciliation")'),
            'limit' => $schema->integer()->description('Max results to return (default 25, max 100)'),
        ];
    }
}
