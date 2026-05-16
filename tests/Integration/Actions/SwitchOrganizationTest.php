<?php

declare(strict_types=1);

use App\Actions\CreateOrganization;
use App\Actions\SwitchOrganization;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('SwitchOrganization action', function () {

    it('updates the current_organization_id of the user', function () {
        $user = User::factory()->create();
        $org1 = app(CreateOrganization::class)->handle($user, 'Org 1');
        $org2 = app(CreateOrganization::class)->handle($user, 'Org 2');

        app(SwitchOrganization::class)->handle($user, $org1);

        expect($user->fresh()->current_organization_id)->toBe($org1->id);
    });

    it('allows switching back and forth between organizations', function () {
        $user = User::factory()->create();
        $org1 = app(CreateOrganization::class)->handle($user, 'Alpha');
        $org2 = app(CreateOrganization::class)->handle($user, 'Beta');
        $action = app(SwitchOrganization::class);

        $action->handle($user, $org1);
        expect($user->fresh()->current_organization_id)->toBe($org1->id);

        $action->handle($user, $org2);
        expect($user->fresh()->current_organization_id)->toBe($org2->id);
    });

    it('throws AuthorizationException when user is not a member', function () {
        $outsider = User::factory()->create();
        $org = Organization::factory()->create();

        expect(fn () => app(SwitchOrganization::class)->handle($outsider, $org))
            ->toThrow(AuthorizationException::class);
    });

    it('does not update current_organization_id when not a member', function () {
        $outsider = User::factory()->create();
        $original = app(CreateOrganization::class)->handle($outsider, 'My Org');
        $other = Organization::factory()->create();

        try {
            app(SwitchOrganization::class)->handle($outsider, $other);
        } catch (AuthorizationException) {
        }

        expect($outsider->fresh()->current_organization_id)->toBe($original->id);
    });
});
