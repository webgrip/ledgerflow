<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class SwitchOrganization
{
    /**
     * @throws AuthorizationException
     */
    public function handle(User $user, Organization $organization): void
    {
        if (! $organization->hasUser($user)) {
            throw new AuthorizationException('You are not a member of this organization.');
        }

        $user->update(['current_organization_id' => $organization->id]);
    }
}
