<?php

declare(strict_types=1);

use App\Actions\CreateOrganization;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

describe('OrganizationPolicy', function () {

    describe('viewAny', function () {
        it('allows any authenticated user to list organizations', function () {
            $user = User::factory()->create();
            expect(Gate::forUser($user)->allows('viewAny', Organization::class))->toBeTrue();
        });
    });

    describe('view', function () {
        it('allows a member to view the organization', function () {
            $user = User::factory()->create();
            $org = app(CreateOrganization::class)->handle($user, 'Acme');

            expect(Gate::forUser($user)->allows('view', $org))->toBeTrue();
        });

        it('denies a non-member from viewing the organization', function () {
            $outsider = User::factory()->create();
            $org = Organization::factory()->create();

            expect(Gate::forUser($outsider)->denies('view', $org))->toBeTrue();
        });
    });

    describe('create', function () {
        it('allows any authenticated user to create an organization', function () {
            $user = User::factory()->create();
            expect(Gate::forUser($user)->allows('create', Organization::class))->toBeTrue();
        });
    });

    describe('update', function () {
        it('allows the owner to update the organization', function () {
            $owner = User::factory()->create();
            $org = app(CreateOrganization::class)->handle($owner, 'Acme');

            expect(Gate::forUser($owner)->allows('update', $org))->toBeTrue();
        });

        it('denies a regular member from updating', function () {
            $owner = User::factory()->create();
            $member = User::factory()->create();
            $org = app(CreateOrganization::class)->handle($owner, 'Acme');
            $org->memberships()->create(['user_id' => $member->id, 'role' => 'member']);

            expect(Gate::forUser($member)->denies('update', $org))->toBeTrue();
        });

        it('denies non-members from updating', function () {
            $outsider = User::factory()->create();
            $org = Organization::factory()->create();

            expect(Gate::forUser($outsider)->denies('update', $org))->toBeTrue();
        });
    });

    describe('delete', function () {
        it('allows the owner to delete the organization', function () {
            $owner = User::factory()->create();
            $org = app(CreateOrganization::class)->handle($owner, 'Acme');

            expect(Gate::forUser($owner)->allows('delete', $org))->toBeTrue();
        });

        it('denies a regular member from deleting', function () {
            $owner = User::factory()->create();
            $member = User::factory()->create();
            $org = app(CreateOrganization::class)->handle($owner, 'Acme');
            $org->memberships()->create(['user_id' => $member->id, 'role' => 'member']);

            expect(Gate::forUser($member)->denies('delete', $org))->toBeTrue();
        });
    });

    describe('manage', function () {
        it('allows the owner to manage members', function () {
            $owner = User::factory()->create();
            $org = app(CreateOrganization::class)->handle($owner, 'Acme');

            expect(Gate::forUser($owner)->allows('manage', $org))->toBeTrue();
        });

        it('denies regular members from managing other members', function () {
            $owner = User::factory()->create();
            $member = User::factory()->create();
            $org = app(CreateOrganization::class)->handle($owner, 'Acme');
            $org->memberships()->create(['user_id' => $member->id, 'role' => 'member']);

            expect(Gate::forUser($member)->denies('manage', $org))->toBeTrue();
        });
    });
});
