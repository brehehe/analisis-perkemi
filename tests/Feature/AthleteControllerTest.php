<?php

use App\Models\Athlete;
use App\Models\Club;
use Inertia\Testing\AssertableInertia as Assert;

it('lists athletes for an authorized user', function () {
    $user = userWithPermissions(['athletes.view']);
    Athlete::factory()->create(['name' => 'Raka Pratama']);

    $this->actingAs($user)
        ->get('/athletes')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Athletes/Index')
            ->has('athletes.data', 1)
            ->where('athletes.data.0.name', 'Raka Pratama')
        );
});

it('creates an athlete and records the audit trail', function () {
    $user = userWithPermissions(['athletes.create']);
    $club = Club::factory()->create();

    $response = $this->actingAs($user)->post('/athletes', [
        'club_id' => $club->getKey(),
        'coach_id' => null,
        'identifier' => 'ATL-9001',
        'name' => 'Satria Wibawa',
        'gender' => 'male',
        'date_of_birth' => '2004-05-02',
        'category' => 'Randori Putra',
        'weight_class' => 60,
        'experience_years' => 5,
        'status' => 'active',
    ]);

    $athlete = Athlete::query()->where('identifier', 'ATL-9001')->firstOrFail();
    $response->assertRedirect("/athletes/{$athlete->getKey()}");
    $this->assertModelExists($athlete);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->getKey(),
        'action' => 'athlete.created',
        'auditable_id' => $athlete->getKey(),
    ]);
});

it('returns validation errors for missing athlete data', function () {
    $user = userWithPermissions(['athletes.create']);

    $this->actingAs($user)
        ->from('/athletes/create')
        ->post('/athletes', [])
        ->assertRedirect('/athletes/create')
        ->assertSessionHasErrors(['identifier', 'name', 'gender', 'category', 'experience_years', 'status']);

    $this->assertDatabaseCount('athletes', 0);
});

it('forbids creating athletes without permission', function () {
    $user = userWithPermissions([]);

    $this->actingAs($user)->post('/athletes', [])->assertForbidden();
    $this->assertDatabaseCount('athletes', 0);
});

it('soft deletes an athlete and records the archive action', function () {
    $user = userWithPermissions(['athletes.delete']);
    $athlete = Athlete::factory()->create();

    $this->actingAs($user)->delete("/athletes/{$athlete->getKey()}")->assertRedirect('/athletes');

    $this->assertSoftDeleted($athlete);
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'athlete.archived',
        'auditable_id' => $athlete->getKey(),
    ]);
});
