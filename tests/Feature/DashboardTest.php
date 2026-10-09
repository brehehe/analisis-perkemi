<?php

use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('forbids users without dashboard permission', function () {
    $user = userWithPermissions([]);

    $this->actingAs($user)->get('/dashboard')->assertForbidden();
});

it('renders operational metrics for authorized users', function () {
    $user = userWithPermissions(['dashboard.view']);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('stats.athletes')
            ->has('processing.queued')
            ->has('recentMatches')
        );
});
