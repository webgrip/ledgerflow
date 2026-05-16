<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\RunReconciliation;
use App\Models\Organization;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class ReconcileOrganization implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public int $tries = 1;

    public function __construct(
        public readonly int $organizationId,
        public readonly string $periodStart,
        public readonly string $periodEnd,
        public readonly ?int $initiatorId = null,
    ) {}

    public function handle(RunReconciliation $action): void
    {
        $organization = Organization::findOrFail($this->organizationId);
        $initiator = $this->initiatorId ? User::find($this->initiatorId) : null;

        $action->handle(
            organization: $organization,
            periodStart: CarbonImmutable::parse($this->periodStart),
            periodEnd: CarbonImmutable::parse($this->periodEnd),
            initiator: $initiator,
        );
    }
}
