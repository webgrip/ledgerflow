<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Authentication flows', function () {

    describe('Login page', function () {
        it('is accessible to guests', function () {
            $this->get(route('login'))->assertOk();
        });

        it('redirects authenticated users away from the login page', function () {
            $user = User::factory()->create();
            $this->actingAs($user)->get(route('login'))->assertRedirect();
        });
    });

    describe('Login action', function () {
        it('authenticates a user with valid credentials', function () {
            $user = User::factory()->create(['password' => bcrypt('secret123')]);

            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'secret123',
            ])->assertRedirect();

            $this->assertAuthenticatedAs($user);
        });

        it('rejects invalid credentials', function () {
            $user = User::factory()->create(['password' => bcrypt('secret123')]);

            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrongpassword',
            ])->assertSessionHasErrors('email');

            $this->assertGuest();
        });

        it('rejects a non-existent email', function () {
            $this->post(route('login.store'), [
                'email' => 'nobody@example.com',
                'password' => 'password',
            ])->assertSessionHasErrors('email');
        });
    });

    describe('Registration page', function () {
        it('is accessible to guests', function () {
            $this->get(route('register'))->assertOk();
        });

        it('redirects authenticated users away from the registration page', function () {
            $user = User::factory()->create();
            $this->actingAs($user)->get(route('register'))->assertRedirect();
        });
    });

    describe('Registration action', function () {
        it('creates a new user with valid data', function () {
            $this->post(route('register.store'), [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])->assertRedirect();

            $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
        });

        it('rejects a duplicate email', function () {
            User::factory()->create(['email' => 'taken@example.com']);

            $this->post(route('register.store'), [
                'name' => 'Another User',
                'email' => 'taken@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])->assertSessionHasErrors('email');
        });

        it('rejects mismatched passwords', function () {
            $this->post(route('register.store'), [
                'name' => 'User',
                'email' => 'user@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different',
            ])->assertSessionHasErrors('password');
        });

        it('requires all fields', function () {
            $this->post(route('register.store'), [])
                ->assertSessionHasErrors(['name', 'email', 'password']);
        });
    });

    describe('Logout', function () {
        it('logs the user out and redirects', function () {
            $user = User::factory()->create();

            $this->actingAs($user)
                ->post(route('logout'))
                ->assertRedirect();

            $this->assertGuest();
        });
    });

    describe('Dashboard access control', function () {
        it('redirects unauthenticated users to login', function () {
            $this->get(route('dashboard'))->assertRedirect(route('login'));
        });

        it('allows authenticated users to access the dashboard', function () {
            $user = User::factory()->create();
            $this->actingAs($user)->get(route('dashboard'))->assertOk();
        });
    });
});
