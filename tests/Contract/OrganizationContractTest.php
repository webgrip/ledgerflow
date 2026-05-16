<?php

declare(strict_types=1);

use App\Actions\CreateOrganization;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Contract tests for Organization — the invariants that the rest of the system depends on.
 */
describe('Organization data contract', function () {

    it('always has a name and a created_by user', function () {
        $org = Organization::factory()->create();

        expect($org->name)->toBeString()->not->toBeEmpty()
            ->and($org->created_by)->not->toBeNull();
    });

    it('always has at least one membership after creation via CreateOrganization', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');

        expect($org->memberships()->count())->toBeGreaterThanOrEqual(1);
    });

    it('the creator is always assigned the Owner role', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');

        $membership = $org->memberships()->where('user_id', $user->id)->first();

        expect($membership->role)->toBe(OrganizationRole::Owner);
    });

    it('hasUser() returns true for members', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');

        expect($org->hasUser($user))->toBeTrue();
    });

    it('hasUser() returns false for non-members', function () {
        $outsider = User::factory()->create();
        $org = Organization::factory()->create();

        expect($org->hasUser($outsider))->toBeFalse();
    });

    it('ownerOf() returns true for the owner', function () {
        $user = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($user, 'Acme');

        expect($org->ownerOf($user))->toBeTrue();
    });

    it('ownerOf() returns false for regular members', function () {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $org = app(CreateOrganization::class)->handle($owner, 'Acme');

        $org->memberships()->create(['user_id' => $member->id, 'role' => OrganizationRole::Member]);

        expect($org->ownerOf($member))->toBeFalse();
    });

    it('ownerOf() returns false for non-members', function () {
        $outsider = User::factory()->create();
        $org = Organization::factory()->create();

        expect($org->ownerOf($outsider))->toBeFalse();
    });

    it('has timestamps (created_at, updated_at)', function () {
        $org = Organization::factory()->create();

        expect($org->created_at)->not->toBeNull()
            ->and($org->updated_at)->not->toBeNull();
    });
});
