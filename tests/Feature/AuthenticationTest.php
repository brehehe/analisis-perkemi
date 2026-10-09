<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the login page for guests', function () {
    $this->get('/login')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
});

it('authenticates a user with valid credentials', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'secret-password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials with a visible error', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    $this->from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'incorrect-password',
    ])->assertRedirect('/login')->assertSessionHasErrors([
        'email' => 'Email atau kata sandi tidak sesuai.',
    ]);

    $this->assertGuest();
});

it('logs an authenticated user out and invalidates the session', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/logout')->assertRedirect('/login');

    $this->assertGuest();
});
