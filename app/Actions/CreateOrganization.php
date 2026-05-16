<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateOrganization
{
    public function handle(User $user, string $name): Organization
    {
        return DB::transaction(function () use ($user, $name): Organization {
            $organization = Organization::create([
                'name' => $name,
                'created_by' => $user->id,
            ]);

            $organization->memberships()->create([
                'user_id' => $user->id,
                'role' => OrganizationRole::Owner,
            ]);

            $user->update(['current_organization_id' => $organization->id]);

            return $organization;
        });
    }
}
