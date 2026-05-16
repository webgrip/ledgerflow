<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Organization;

class CreateAccount
{
    public function handle(
        Organization $organization,
        string $name,
        AccountType $type,
        string $currency = 'USD',
        ?string $description = null,
    ): Account {
        return Account::create([
            'organization_id' => $organization->id,
            'name' => $name,
            'type' => $type,
            'currency' => $currency,
            'description' => $description,
        ]);
    }
}
