<?php

use App\Models\Athlete;
use App\Models\MatchRecord;

it('creates a match and records the audit trail', function () {
    $user = userWithPermissions(['matches.create']);
    $athlete = Athlete::factory()->create(['gender' => 'male']);

    $response = $this->actingAs($user)->post('/matches', [
        'competition_event_id' => null,
        'athlete_ids' => [$athlete->getKey()],
        'opponent_name' => 'Dimas Arya',
        'opponent_club' => 'Dojo Garuda',
        'match_date' => '2026-09-21 14:30:00',
        'match_type' => 'randori',
        'division' => 'male',
        'result' => 'pending',
        'athlete_score' => null,
        'opponent_score' => null,
        'status' => 'scheduled',
        'notes' => null,
    ]);

    $response->assertSessionHasNoErrors();
    $match = MatchRecord::query()->where('opponent_name', 'Dimas Arya')->firstOrFail();
    $response->assertRedirect("/matches/{$match->getKey()}");
    $this->assertModelExists($match);
    $this->assertDatabaseHas('match_records', [
        'id' => $match->getKey(),
        'athlete_id' => $athlete->getKey(),
        'category' => 'Randori Putra',
        'division' => 'male',
    ]);
    $this->assertDatabaseHas('athlete_match_record', [
        'match_record_id' => $match->getKey(),
        'athlete_id' => $athlete->getKey(),
        'position' => 1,
    ]);
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'match.created',
        'auditable_id' => $match->getKey(),
    ]);
});

it('keeps the redirect secure when TLS is terminated by a trusted proxy', function () {
    $user = userWithPermissions(['matches.create']);
    $athlete = Athlete::factory()->create(['gender' => 'male']);

    $response = $this->withServerVariables([
        'REMOTE_ADDR' => '127.0.0.1',
        'HTTP_X_FORWARDED_PROTO' => 'https',
    ])->actingAs($user)->post('http://analisis.giradia.id/matches', matchPayload([
        'athlete_ids' => [$athlete->getKey()],
        'match_type' => 'randori',
        'division' => 'male',
        'opponent_name' => 'Dimas Arya',
    ]));

    $match = MatchRecord::query()->where('opponent_name', 'Dimas Arya')->firstOrFail();
    $response->assertRedirect("https://analisis.giradia.id/matches/{$match->getKey()}");
});

it('rejects a match with an unknown athlete', function () {
    $user = userWithPermissions(['matches.create']);

    $this->actingAs($user)->post('/matches', [
        'athlete_ids' => ['01J00000000000000000000000'],
        'opponent_name' => 'Dimas Arya',
        'match_date' => '2026-09-21 14:30:00',
        'match_type' => 'randori',
        'division' => 'male',
        'result' => 'pending',
        'status' => 'scheduled',
    ])->assertSessionHasErrors('athlete_ids.0');

    $this->assertDatabaseCount('match_records', 0);
});

it('creates an Embu Perorangan entry without an opponent', function () {
    $user = userWithPermissions(['matches.create']);
    $athlete = Athlete::factory()->create(['gender' => 'female']);

    $response = $this->actingAs($user)->post('/matches', matchPayload([
        'athlete_ids' => [$athlete->getKey()],
        'match_type' => 'embu_individual',
        'division' => 'female',
        'opponent_name' => 'Data yang harus diabaikan',
        'opponent_score' => 99,
    ]));

    $match = MatchRecord::query()->latest('id')->firstOrFail();
    $response->assertRedirect("/matches/{$match->getKey()}");
    expect($match->opponent_name)->toBeNull()
        ->and($match->opponent_score)->toBeNull()
        ->and($match->category)->toBe('Embu Perorangan Putri')
        ->and($match->athletes()->pluck('athletes.id')->all())->toBe([$athlete->getKey()]);
});

it('creates an Embu Pasangan Campuran with two athletes', function () {
    $user = userWithPermissions(['matches.create']);
    $maleAthlete = Athlete::factory()->create(['gender' => 'male']);
    $femaleAthlete = Athlete::factory()->create(['gender' => 'female']);

    $response = $this->actingAs($user)->post('/matches', matchPayload([
        'athlete_ids' => [$maleAthlete->getKey(), $femaleAthlete->getKey()],
        'match_type' => 'embu_pair',
        'division' => 'mixed',
    ]));

    $match = MatchRecord::query()->latest('id')->firstOrFail();
    $response->assertRedirect("/matches/{$match->getKey()}");
    expect($match->category)->toBe('Embu Pasangan Campuran')
        ->and($match->athletes()->pluck('athletes.id')->all())->toBe([
            $maleAthlete->getKey(),
            $femaleAthlete->getKey(),
        ]);
});

it('creates an Embu Beregu Putra with four athletes', function () {
    $user = userWithPermissions(['matches.create']);
    $athletes = Athlete::factory()->count(4)->create(['gender' => 'male']);

    $response = $this->actingAs($user)->post('/matches', matchPayload([
        'athlete_ids' => $athletes->modelKeys(),
        'match_type' => 'embu_team',
        'division' => 'male',
    ]));

    $match = MatchRecord::query()->latest('id')->firstOrFail();
    $response->assertRedirect("/matches/{$match->getKey()}");
    expect($match->category)->toBe('Embu Beregu Putra')
        ->and($match->athletes()->count())->toBe(4);
});

it('rejects a team whose athlete count does not match its Embu format', function () {
    $user = userWithPermissions(['matches.create']);
    $athletes = Athlete::factory()->count(2)->create(['gender' => 'male']);

    $this->actingAs($user)
        ->post('/matches', matchPayload([
            'athlete_ids' => $athletes->modelKeys(),
            'match_type' => 'embu_team',
            'division' => 'male',
        ]))
        ->assertSessionHasErrors([
            'athlete_ids' => 'Jenis pertandingan ini membutuhkan tepat 4 atlet.',
        ]);

    $this->assertDatabaseCount('match_records', 0);
});

it('rejects a mixed Embu team without both genders', function () {
    $user = userWithPermissions(['matches.create']);
    $athletes = Athlete::factory()->count(2)->create(['gender' => 'male']);

    $this->actingAs($user)
        ->post('/matches', matchPayload([
            'athlete_ids' => $athletes->modelKeys(),
            'match_type' => 'embu_pair',
            'division' => 'mixed',
        ]))
        ->assertSessionHasErrors([
            'athlete_ids' => 'Kategori Campuran harus memuat atlet putra dan putri.',
        ]);

    $this->assertDatabaseCount('match_records', 0);
});

it('rejects Campuran for a one-person Embu category', function () {
    $user = userWithPermissions(['matches.create']);
    $athlete = Athlete::factory()->create(['gender' => 'male']);

    $this->actingAs($user)
        ->post('/matches', matchPayload([
            'athlete_ids' => [$athlete->getKey()],
            'match_type' => 'embu_individual',
            'division' => 'mixed',
        ]))
        ->assertSessionHasErrors([
            'division' => 'Kategori Campuran hanya tersedia untuk Embu Pasangan atau Embu Beregu.',
        ]);

    $this->assertDatabaseCount('match_records', 0);
});

it('updates a Randori record into an Embu Pasangan team', function () {
    $user = userWithPermissions(['matches.update']);
    $maleAthlete = Athlete::factory()->create(['gender' => 'male']);
    $femaleAthlete = Athlete::factory()->create(['gender' => 'female']);
    $match = MatchRecord::factory()->create([
        'athlete_id' => $maleAthlete->getKey(),
        'opponent_name' => 'Lawan Lama',
        'opponent_score' => 3,
    ]);

    $this->actingAs($user)
        ->put("/matches/{$match->getKey()}", matchPayload([
            'athlete_ids' => [$maleAthlete->getKey(), $femaleAthlete->getKey()],
            'match_type' => 'embu_pair',
            'division' => 'mixed',
        ]))
        ->assertRedirect("/matches/{$match->getKey()}")
        ->assertSessionHasNoErrors();

    $match->refresh();
    expect($match->category)->toBe('Embu Pasangan Campuran')
        ->and($match->opponent_name)->toBeNull()
        ->and($match->opponent_score)->toBeNull()
        ->and($match->athletes()->pluck('athletes.id')->all())->toBe([
            $maleAthlete->getKey(),
            $femaleAthlete->getKey(),
        ]);
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'match.updated',
        'auditable_id' => $match->getKey(),
    ]);
});

/** @param array<string, mixed> $overrides */
function matchPayload(array $overrides = []): array
{
    return [
        'competition_event_id' => null,
        'opponent_name' => null,
        'opponent_club' => null,
        'match_date' => '2026-09-21 14:30:00',
        'match_type' => 'embu_individual',
        'division' => 'male',
        'result' => 'pending',
        'athlete_score' => null,
        'opponent_score' => null,
        'status' => 'scheduled',
        'notes' => null,
        ...$overrides,
    ];
}
