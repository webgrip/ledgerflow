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

    /** Org members may create accounts in their current org. */
    public function create(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    /** Only org members may update accounts. */
    public function update(User $user, Account $account): bool
    {
        return $account->organization->hasUser($user);
    }

    /** Only org members may delete accounts. */
    public function delete(User $user, Account $account): bool
    {
        return $account->organization->hasUser($user);
    }
}
