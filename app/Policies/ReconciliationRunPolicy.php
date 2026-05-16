<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ReconciliationRun;
use App\Models\User;

class ReconciliationRunPolicy
{
    /** Any org member may view reconciliation runs. */
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    /** Any org member may view a specific run belonging to their org. */
    public function view(User $user, ReconciliationRun $run): bool
    {
        return $run->organization_id === $user->current_organization_id;
    }

    /** Only org owners may trigger reconciliation runs. */
    public function create(User $user): bool
    {
        return $user->current_organization_id !== null
            && $user->currentOrganization?->ownerOf($user) === true;
    }
}
