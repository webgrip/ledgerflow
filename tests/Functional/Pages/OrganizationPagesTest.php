<?php

declare(strict_types=1);

use App\Actions\CreateOrganization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('Organization create page', function () {
    it('redirects guests to login', function () {
        $this->get(route('organizations.create'))->assertRedirect(route('login'));
    });

    it('returns 200 for authenticated users', function () {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('organizations.create'))->assertOk();
    });

    it('creates an organization via the Livewire component', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::organizations.create')
            ->set('name', 'New Corp')
            ->call('create')
            ->assertHasNoErrors()
            ->assertRedirectContains('dashboard');

        $this->assertDatabaseHas('organizations', ['name' => 'New Corp']);
        expect($user->fresh()->current_organization_id)->not->toBeNull();
    });

    it('fails validation when name is too short', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::organizations.create')
            ->set('name', 'A') // min 2 chars
            ->call('create')
            ->assertHasErrors(['name']);
    });

    it('fails validation when name is missing', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::organizations.create')
            ->call('create')
            ->assertHasErrors(['name']);
    });
});

describe('Org switcher component', function () {
    it('renders without errors for a user with organizations', function () {
        $user = User::factory()->create();
        app(CreateOrganization::class)->handle($user, 'Acme');

        Livewire::actingAs($user)
            ->test('organizations.org-switcher')
            ->assertOk();
    });

    it('renders without errors for a user with no organizations', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('organizations.org-switcher')
            ->assertOk();
    });
});
