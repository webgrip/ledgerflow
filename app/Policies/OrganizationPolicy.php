<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /** Any authenticated user may list their own organizations. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Members and owners may view the organization. */
    public function view(User $user, Organization $organization): bool
    {
        return $organization->hasUser($user);
    }

    /** Any authenticated user may create an organization. */
    public function create(User $user): bool
    {
        return true;
    }

    /** Only owners may rename or reconfigure the organization. */
    public function update(User $user, Organization $organization): bool
    {
        return $organization->ownerOf($user);
    }

    /** Only owners may delete the organization. */
    public function delete(User $user, Organization $organization): bool
    {
        return $organization->ownerOf($user);
    }

    /** Only owners may manage (invite/remove) members. */
    public function manage(User $user, Organization $organization): bool
    {
        return $organization->ownerOf($user);
    }
}
