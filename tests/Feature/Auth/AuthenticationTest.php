<?php

// SPEC: User Authentication
// This test file serves as executable specification for user login/logout.
// See specs/features/authentication.md for full feature spec.

use App\Models\User;

describe('Authentication - Happy Path', function () {
    it('renders the login page for guests', function () {
        $this->get('/login')
            ->assertSuccessful()
            ->assertSee('Log in');
    });

    it('authenticates a user with valid email and password', function () {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    });

    it('allows authenticated users to access the dashboard', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSuccessful();
    });

    it('logs out the user and invalidates the session', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    });

    it('prevents access to protected pages after logout', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/logout');

        $this->get('/dashboard')
            ->assertRedirect('/login');
    });
});

describe('Authentication - Validation', function () {
    it('fails with empty email', function () {
        $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    });

    it('fails with empty password', function () {
        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => '',
        ])
            ->assertSessionHasErrors('password');

        $this->assertGuest();
    });

    it('fails with wrong password', function () {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    });

    it('fails with non-existent email', function () {
        $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
    });
});

describe('Authentication - Edge Cases', function () {
    it('redirects guests from /dashboard to /login', function () {
        $this->get('/dashboard')
            ->assertRedirect('/login');
    });

    it('redirects authenticated users from /login to /dashboard', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect('/dashboard');
    });

    it('redirects root path to /login', function () {
        $this->get('/')
            ->assertRedirect('/login');
    });

    it('rate-limits login after too many failed attempts', function () {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertStatus(429);
    });
});
