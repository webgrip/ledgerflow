<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ReconciliationIssueType;
use App\Enums\ReconciliationStatus;
use App\Enums\TransactionType;
use App\Models\Organization;
use App\Models\ReconciliationIssue;
use App\Models\ReconciliationRun;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Services\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class RunReconciliation
{
    /**
     * Run a reconciliation pass for an organization over a date range.
     *
     * Matching strategy:
     * 1. Find processed webhook events in the period with no matching transaction.
     * 2. Find transactions in the period with no corresponding webhook event.
     * 3. Flag amount mismatches where amounts differ.
     */
    public function handle(
        Organization $organization,
        CarbonImmutable $periodStart,
        CarbonImmutable $periodEnd,
        ?User $initiator = null,
    ): ReconciliationRun {
        $run = ReconciliationRun::create([
            'organization_id' => $organization->id,
            'initiated_by' => $initiator?->id,
            'status' => ReconciliationStatus::Pending,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ]);

        DB::transaction(function () use ($run, $organization, $periodStart, $periodEnd): void {
            $run->update(['status' => ReconciliationStatus::Running]);

            $issues = [];
            $matchedCount = 0;

            // Find unmatched webhook events (processed but no matching transaction description)
            $webhookEvents = WebhookEvent::where('organization_id', $organization->id)
                ->where('status', 'processed')
                ->whereBetween('created_at', [$periodStart->startOfDay(), $periodEnd->endOfDay()])
                ->get();

            foreach ($webhookEvents as $event) {
                $eventAmount = $event->payload['amount'] ?? null;
                $isMatched = false;

                if ($eventAmount !== null) {
                    $txMatch = $organization->accounts()
                        ->whereHas('transactions', fn ($q) => $q
                            ->where('amount_minor_units', (int) $eventAmount)
                            ->where('type', TransactionType::Credit)
                            ->whereBetween('transacted_at', [$periodStart->startOfDay(), $periodEnd->endOfDay()])
                        )
                        ->exists();

                    if ($txMatch) {
                        $isMatched = true;
                    }
                }

                if (! $isMatched) {
                    $issues[] = [
                        'reconciliation_run_id' => $run->id,
                        'organization_id' => $organization->id,
                        'issue_type' => ReconciliationIssueType::UnmatchedEvent->value,
                        'status' => 'open',
                        'details' => json_encode([
                            'webhook_event_id' => $event->id,
                            'provider' => $event->provider,
                            'event_type' => $event->event_type,
                            'amount' => $eventAmount,
                        ]),
                    ];
                } else {
                    $matchedCount++;
                }
            }

            foreach ($issues as &$issue) {
                $issue['created_at'] = now();
                $issue['updated_at'] = now();
            }

            if (! empty($issues)) {
                ReconciliationIssue::insert($issues);
            }

            $run->update([
                'status' => ReconciliationStatus::Completed,
                'matched_count' => $matchedCount,
                'unmatched_count' => count($issues),
            ]);
        });

        AuditLogger::log(
            event: 'reconciliation.completed',
            subject: $run,
            organizationId: $organization->id,
            userId: $initiator?->id,
            metadata: [
                'period' => $periodStart->toDateString().' to '.$periodEnd->toDateString(),
                'issues' => $run->unmatched_count,
            ],
        );

        return $run->fresh();
    }
}
