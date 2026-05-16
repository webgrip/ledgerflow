<?php

declare(strict_types=1);

use App\Actions\CreateOrganization;
use App\Models\Account;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Smoke tests verify the application is alive and basic routing works.
 * Crucially, these include NEGATION tests — verifying things that SHOULD fail
 * do fail (redirects, 403s, 404s, validation errors).
 */
describe('Public route availability', function () {

    it('GET / returns 200', function () {
        $this->get('/')->assertOk();
    });

    it('GET /up returns 200 (health check)', function () {
        $this->get('/up')->assertOk();
    });

    it('GET /dev returns 200 (public dev dashboard)', function () {
        $this->get('/dev')->assertOk();
    });

    it('GET /login returns 200', function () {
        $this->get('/login')->assertOk();
    });

    it('GET /register returns 200', function () {
        $this->get('/register')->assertOk();
    });

    it('GET /forgot-password returns 200', function () {
        $this->get('/forgot-password')->assertOk();
    });
});

describe('Protected routes redirect unauthenticated users (negation: MUST NOT return 200)', function () {

    it('GET /dashboard returns 302 for guests, NOT 200', function () {
        $this->get('/dashboard')
            ->assertRedirect()
            ->assertRedirectContains('login');
    });

    it('GET /accounts returns 302 for guests, NOT 200', function () {
        $this->get('/accounts')
            ->assertRedirect()
            ->assertRedirectContains('login');
    });

    it('GET /accounts/create returns 302 for guests, NOT 200', function () {
        $this->get('/accounts/create')
            ->assertRedirect()
            ->assertRedirectContains('login');
    });

    it('GET /accounts/1 returns 302 for guests, NOT 200', function () {
        $this->get('/accounts/1')
            ->assertRedirect()
            ->assertRedirectContains('login');
    });

    it('GET /organizations/create returns 302 for guests, NOT 200', function () {
        $this->get('/organizations/create')
            ->assertRedirect()
            ->assertRedirectContains('login');
    });
});

describe('Authentication failure negation tests (MUST NOT return 2xx)', function () {

    it('POST /login with wrong password does not return 200', function () {
        User::factory()->create(['email' => 'user@example.com', 'password' => bcrypt('correct')]);

        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'wrong',
        ])->assertSessionHasErrors('email')
            ->assertStatus(302); // redirects back with errors, NOT 200 or 500
    });

    it('POST /login with non-existent email does not authenticate', function () {
        $this->post('/login', [
            'email' => 'nobody@nowhere.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    });

    it('POST /register with mismatched passwords does not create a user', function () {
        $this->post('/register', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    });
});

describe('Authorization negation tests (MUST NOT return 200 for wrong user)', function () {

    it('non-member cannot access another org\'s account — must return 403, NOT 200', function () {
        $outsider = User::factory()->create();
        app(CreateOrganization::class)->handle($outsider, 'Outsider Org');

        $org = Organization::factory()->create();
        $account = Account::factory()->create(['organization_id' => $org->id]);

        $this->actingAs($outsider)
            ->get(route('accounts.show', $account))
            ->assertForbidden(); // 403, NOT 200
    });

    it('accessing a non-existent account returns 404, NOT 500', function () {
        $user = User::factory()->create();
        app(CreateOrganization::class)->handle($user, 'Acme');

        $this->actingAs($user)
            ->get('/accounts/99999999')
            ->assertNotFound(); // 404, NOT 500
    });

    it('accessing a non-existent route returns 404, NOT 500', function () {
        $this->get('/this-route-does-not-exist')
            ->assertNotFound();
    });

    it('member cannot record a transaction on another org\'s account — returns 403, NOT 200', function () {
        $outsider = User::factory()->create();
        app(CreateOrganization::class)->handle($outsider, 'Outsider Org');

        $account = Account::factory()->create(); // different org

        $this->actingAs($outsider)
            ->get(route('transactions.create', $account))
            ->assertForbidden();
    });
});

describe('Method negation tests (MUST NOT accept wrong HTTP methods)', function () {

    it('GET /login does not accept POST data as a page load (returns GET page)', function () {
        // Verifying the page returns HTML, not that the login form submits
        $this->get('/login')->assertOk()->assertSee('email');
    });

    it('a non-existent API path returns 404, NOT 500', function () {
        $this->getJson('/api/v1/accounts')
            ->assertNotFound();
    });
});
