<?php

use App\Jobs\PrepareVideoAnalysisJob;
use App\Models\Analysis;
use App\Models\MatchRecord;
use Illuminate\Support\Facades\Queue;

it('rejects an unsupported external video host', function () {
    $user = userWithPermissions(['videos.upload', 'matches.update']);
    $match = MatchRecord::factory()->create();

    $this->actingAs($user)
        ->from("/matches/{$match->getKey()}")
        ->post("/matches/{$match->getKey()}/videos", [
            'source' => 'youtube',
            'youtube_url' => 'https://example.com/video',
        ])
        ->assertRedirect("/matches/{$match->getKey()}")
        ->assertSessionHasErrors([
            'youtube_url' => 'Gunakan URL video YouTube yang valid.',
        ]);

    $this->assertDatabaseCount('videos', 0);
    $this->assertDatabaseCount('analyses', 0);
});

it('stores a YouTube video and queues analysis preparation', function () {
    Queue::fake([PrepareVideoAnalysisJob::class]);
    $user = userWithPermissions(['videos.upload', 'matches.update']);
    $match = MatchRecord::factory()->create();

    $response = $this->actingAs($user)->post("/matches/{$match->getKey()}/videos", [
        'source' => 'youtube',
        'youtube_url' => 'https://www.youtube.com/watch?v=video123',
        'focus_description' => 'Atlet sudut merah dengan pelindung kepala merah.',
    ]);

    $analysis = Analysis::query()->firstOrFail();
    $response->assertRedirect("/analyses/{$analysis->getKey()}");
    $this->assertDatabaseHas('videos', [
        'match_record_id' => $match->getKey(),
        'source' => 'youtube',
        'focus_description' => 'Atlet sudut merah dengan pelindung kepala merah.',
    ]);
    $this->assertDatabaseHas('analyses', [
        'match_record_id' => $match->getKey(),
        'status' => 'queued',
    ]);
    Queue::assertPushed(PrepareVideoAnalysisJob::class, fn (PrepareVideoAnalysisJob $job): bool => $job->analysis->is($analysis));
});

it('rejects a target description longer than one thousand characters', function () {
    Queue::fake([PrepareVideoAnalysisJob::class]);
    $user = userWithPermissions(['videos.upload', 'matches.update']);
    $match = MatchRecord::factory()->create();

    $this->actingAs($user)
        ->from("/matches/{$match->getKey()}")
        ->post("/matches/{$match->getKey()}/videos", [
            'source' => 'youtube',
            'youtube_url' => 'https://www.youtube.com/watch?v=video123',
            'focus_description' => str_repeat('a', 1001),
        ])
        ->assertRedirect("/matches/{$match->getKey()}")
        ->assertSessionHasErrors([
            'focus_description' => 'Petunjuk target maksimal 1.000 karakter.',
        ]);

    $this->assertDatabaseCount('videos', 0);
    Queue::assertNothingPushed();
});
