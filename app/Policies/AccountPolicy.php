<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    /** Org members may list accounts. */
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    /** Only members of the account's org may view it. */
    public function view(User $user, Account $account): bool
    {
        return $account->organization->hasUser($user);
    }

    /** Only org owners may create accounts. */
    public function create(User $user): bool
    {
        return $user->current_organization_id !== null
            && $user->currentOrganization?->ownerOf($user) === true;
    }

    /** Only org owners may update accounts. */
    public function update(User $user, Account $account): bool
    {
        return $account->organization->ownerOf($user);
    }

    /** Only org owners may delete accounts. */
    public function delete(User $user, Account $account): bool
    {
        return $account->organization->ownerOf($user);
    }
}
