<?php

use App\Enums\AnalysisStatus;
use App\Jobs\AnalyzeVideoWithOpenAiJob;
use App\Jobs\DownloadYoutubeVideoJob;
use App\Jobs\PrepareVideoAnalysisJob;
use App\Models\Analysis;
use App\Models\Athlete;
use App\Models\MatchRecord;
use App\Models\Video;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

it('dispatches OpenAI analysis for a private uploaded video', function () {
    Queue::fake([AnalyzeVideoWithOpenAiJob::class]);
    Storage::fake('local');
    Storage::disk('local')->put('match-videos/test.mp4', 'video-content');
    $athlete = Athlete::factory()->create();
    $match = MatchRecord::factory()->create(['athlete_id' => $athlete->getKey()]);
    $video = Video::factory()->create([
        'match_record_id' => $match->getKey(),
        'source' => 'upload',
        'external_url' => null,
        'storage_path' => 'match-videos/test.mp4',
    ]);
    $analysis = Analysis::factory()->create([
        'match_record_id' => $match->getKey(),
        'video_id' => $video->getKey(),
        'athlete_id' => $athlete->getKey(),
    ]);

    (new PrepareVideoAnalysisJob($analysis))->handle();

    $analysis->refresh();
    expect($analysis->status)->toBe(AnalysisStatus::Queued)
        ->and($analysis->progress)->toBe(10)
        ->and($analysis->current_step)->toBe('Masuk antrean analisis OpenAI');
    Queue::assertPushed(AnalyzeVideoWithOpenAiJob::class, fn (AnalyzeVideoWithOpenAiJob $job): bool => $job->analysis->is($analysis));
});

it('dispatches a YouTube download before OpenAI analysis', function () {
    Queue::fake([DownloadYoutubeVideoJob::class]);
    $athlete = Athlete::factory()->create();
    $match = MatchRecord::factory()->create(['athlete_id' => $athlete->getKey()]);
    $video = Video::factory()->create([
        'match_record_id' => $match->getKey(),
        'source' => 'youtube',
        'external_url' => 'https://www.youtube.com/watch?v=YoZGDqWRLUo',
        'storage_path' => null,
    ]);
    $analysis = Analysis::factory()->create([
        'match_record_id' => $match->getKey(),
        'video_id' => $video->getKey(),
        'athlete_id' => $athlete->getKey(),
    ]);

    (new PrepareVideoAnalysisJob($analysis))->handle();

    $analysis->refresh();
    expect($analysis->status)->toBe(AnalysisStatus::Queued)
        ->and($analysis->progress)->toBe(5)
        ->and($analysis->current_step)->toBe('URL YouTube valid; masuk antrean pengunduhan');
    Queue::assertPushed(DownloadYoutubeVideoJob::class, fn (DownloadYoutubeVideoJob $job): bool => $job->analysis->is($analysis));
});
