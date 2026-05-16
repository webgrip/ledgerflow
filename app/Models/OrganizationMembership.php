<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrganizationRole;
use Database\Factories\OrganizationMembershipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['organization_id', 'user_id', 'role'])]
class OrganizationMembership extends Pivot
{
    /** @use HasFactory<OrganizationMembershipFactory> */
    use HasFactory;

    protected $table = 'organization_memberships';

    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'role' => OrganizationRole::class,
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOwner(): bool
    {
        return $this->role === OrganizationRole::Owner;
    }
}
