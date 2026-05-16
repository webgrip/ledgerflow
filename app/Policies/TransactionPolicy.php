<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /** Org members may view transactions belonging to their accounts. */
    public function view(User $user, Transaction $transaction): bool
    {
        return $transaction->account->organization->hasUser($user);
    }

    /** Any org member may record transactions. */
    public function create(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    /** Only org owners may delete transactions. */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $transaction->account->organization->ownerOf($user);
    }
}
