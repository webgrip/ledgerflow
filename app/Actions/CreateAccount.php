<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Organization;
use App\Models\User;
use App\Services\AuditLogger;

class CreateAccount
{
    public function handle(
        Organization $organization,
        string $name,
        AccountType $type,
        string $currency = 'USD',
        ?string $description = null,
        ?User $actor = null,
    ): Account {
        $account = Account::create([
            'organization_id' => $organization->id,
            'name' => $name,
            'type' => $type,
            'currency' => $currency,
            'description' => $description,
        ]);

        AuditLogger::log(
            event: 'account.created',
            subject: $account,
            organizationId: $organization->id,
            userId: $actor?->id,
            metadata: ['name' => $name, 'type' => $type->value, 'currency' => $currency],
        );

        return $account;
    }
}
