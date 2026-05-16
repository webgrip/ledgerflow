<?php

use App\Actions\CreateOrganization;
use App\Actions\SwitchOrganization;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// --- CreateOrganization action ---

it('creates an organization with the creator as owner', function () {
    $user = User::factory()->create();
    $action = app(CreateOrganization::class);

    $org = $action->handle($user, 'Acme Corp');

    expect($org)->toBeInstanceOf(Organization::class)
        ->and($org->name)->toBe('Acme Corp')
        ->and($org->created_by)->toBe($user->id);

    expect(OrganizationMembership::where([
        'organization_id' => $org->id,
        'user_id' => $user->id,
        'role' => OrganizationRole::Owner->value,
    ])->exists())->toBeTrue();
});

it('sets the new organization as the current organization for the creator', function () {
    $user = User::factory()->create();
    $action = app(CreateOrganization::class);

    $org = $action->handle($user, 'My Workspace');

    expect($user->fresh()->current_organization_id)->toBe($org->id);
});

// --- SwitchOrganization action ---

it('switches the current organization for a member', function () {
    $user = User::factory()->create();
    $first = app(CreateOrganization::class)->handle($user, 'First Org');
    $second = app(CreateOrganization::class)->handle($user, 'Second Org');

    app(SwitchOrganization::class)->handle($user, $first);

    expect($user->fresh()->current_organization_id)->toBe($first->id);
});

it('refuses to switch to an organization the user does not belong to', function () {
    $user = User::factory()->create();
    $stranger = Organization::factory()->create();

    expect(fn () => app(SwitchOrganization::class)->handle($user, $stranger))
        ->toThrow(AuthorizationException::class);
});

// --- OrganizationPolicy ---

it('allows any authenticated user to create an organization', function () {
    $user = User::factory()->create();

    expect($user->can('create', Organization::class))->toBeTrue();
});

it('allows a member to view their organization', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Viewable Org');

    expect($user->can('view', $org))->toBeTrue();
});

it('denies a non-member from viewing an organization', function () {
    $outsider = User::factory()->create();
    $org = Organization::factory()->create();

    expect($outsider->can('view', $org))->toBeFalse();
});

it('allows the owner to update the organization', function () {
    $user = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($user, 'Owner Org');

    expect($user->can('update', $org))->toBeTrue();
});

it('denies a regular member from updating the organization', function () {
    $owner = User::factory()->create();
    $org = app(CreateOrganization::class)->handle($owner, 'Shared Org');

    $member = User::factory()->create();
    OrganizationMembership::factory()->create([
        'organization_id' => $org->id,
        'user_id' => $member->id,
        'role' => OrganizationRole::Member,
    ]);

    expect($member->can('update', $org))->toBeFalse();
});

it('denies cross-organization access between unrelated orgs', function () {
    $userA = User::factory()->create();
    $orgA = app(CreateOrganization::class)->handle($userA, 'Org A');

    $userB = User::factory()->create();

    expect($userB->can('view', $orgA))->toBeFalse();
});

// --- Organization create page ---

it('redirects guests from the organization create page', function () {
    $this->get(route('organizations.create'))
        ->assertRedirect(route('login'));
});

it('authenticated users can visit the organization create page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('organizations.create'))
        ->assertOk();
});

it('creates an organization via the Livewire component', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::organizations.create')
        ->set('name', 'My New Org')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    expect(Organization::where('name', 'My New Org')->exists())->toBeTrue();
});

it('validates that organization name is required', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::organizations.create')
        ->set('name', '')
        ->call('create')
        ->assertHasErrors(['name' => 'required']);
});
