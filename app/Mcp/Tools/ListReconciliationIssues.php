<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\ReconciliationIssueStatus;
use App\Models\ReconciliationIssue;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List reconciliation issues for the current organization. Defaults to open issues only. Optionally filter by status (open, resolved, ignored).')]
class ListReconciliationIssues extends Tool
{
    public function handle(Request $request): Response
    {
        $user = Auth::user();
        $orgId = $user?->current_organization_id;

        if ($orgId === null) {
            return Response::text('No organization selected for this user.');
        }

        $statusFilter = $request->get('status', 'open');
        $status = ReconciliationIssueStatus::tryFrom($statusFilter);

        $query = ReconciliationIssue::where('organization_id', $orgId)->with('run');

        if ($status !== null) {
            $query->where('status', $status);
        }

        $issues = $query->orderByDesc('created_at')->limit(50)->get();

        if ($issues->isEmpty()) {
            return Response::text("No reconciliation issues found with status: {$statusFilter}.");
        }

        $lines = $issues->map(fn (ReconciliationIssue $issue): string => sprintf(
            '- [%s] %s | Status: %s | Run: #%d | Details: %s',
            $issue->created_at->format('Y-m-d'),
            $issue->issue_type->label(),
            $issue->status->value,
            $issue->reconciliation_run_id,
            json_encode($issue->details),
        ))->join("\n");

        return Response::text("Found {$issues->count()} issue(s) with status '{$statusFilter}':\n{$lines}");
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()->description('Filter by status: open (default), resolved, or ignored'),
        ];
    }
}
